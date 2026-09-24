<?php

namespace App\Services;

use App\Models\AtkRequest;
use App\Models\AtkRequestItem;
use App\Models\AtkRequestHistory;
use App\Models\AtkVariant;
use App\Models\AtkStockMovement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AtkRequestService
{
    /**
     * Create a new request.
     */
    public function createRequest(int $userId, array $itemsData, ?string $note): AtkRequest
    {
        return DB::transaction(function () use ($userId, $itemsData, $note) {
            // Validate items data and stock
            if (empty($itemsData)) {
                throw new \Exception('Keranjang permintaan tidak boleh kosong.');
            }

            // Generate Request Number
            $requestNumber = $this->generateRequestNumber();

            // Create Request
            $request = AtkRequest::create([
                'request_number' => $requestNumber,
                'user_id' => $userId,
                'status' => 'diajukan',
                'note' => $note,
            ]);

            foreach ($itemsData as $item) {
                if (!isset($item['quantity']) || $item['quantity'] <= 0) {
                    throw new \Exception('Jumlah permintaan barang harus lebih besar dari 0.');
                }

                // Acquire write lock to read absolute latest stock
                $variant = AtkVariant::lockForUpdate()
                    ->with(['item.category'])
                    ->findOrFail($item['variant_id']);

                // Check active status
                if (!$variant->is_active || !$variant->item->is_active || !$variant->item->category->is_active) {
                    throw new \Exception("Barang {$variant->item->name} ({$variant->name}) sedang tidak aktif dan tidak dapat diminta.");
                }

                // Double check stock availability
                if ($variant->stock < $item['quantity']) {
                    throw new \Exception("Stok {$variant->item->name} {$variant->name} saat ini hanya tersisa {$variant->stock} {$variant->unit}.");
                }

                AtkRequestItem::create([
                    'request_id' => $request->id,
                    'variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $variant->purchase_price,
                    'subtotal' => $item['quantity'] * $variant->purchase_price,
                ]);
            }

            // Add History
            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'diajukan',
                'notes' => 'Permintaan berhasil diajukan oleh pengguna.',
                'actor_id' => $userId,
            ]);

            // Notify GA Users
            $gaUsers = User::where('role', 'ga')->get();
            foreach ($gaUsers as $ga) {
                Notification::send(
                    $ga->id,
                    'Permintaan ATK Baru',
                    "Permintaan ATK {$requestNumber} telah diajukan oleh " . auth()->user()->name,
                    route('atk.requests.show', $request->id) // link for GA/Admin
                );
            }

            return $request;
        });
    }

    /**
     * Approve a request (GA/Admin).
     */
    public function approveRequest(int $requestId, int $actorId): AtkRequest
    {
        return DB::transaction(function () use ($requestId, $actorId) {
            $request = AtkRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'diajukan') {
                throw new \Exception('Hanya permintaan baru (Diajukan) yang dapat disetujui.');
            }

            $request->update([
                'status' => 'disetujui',
                'approved_by' => $actorId,
                'approved_at' => Carbon::now('Asia/Jakarta'),
            ]);

            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'disetujui',
                'notes' => 'Permintaan disetujui oleh General Affairs.',
                'actor_id' => $actorId,
            ]);

            // Notify Requester
            Notification::send(
                $request->user_id,
                'Permintaan ATK Disetujui',
                "Permintaan ATK {$request->request_number} telah disetujui dan menunggu serah terima.",
                route('atk.requests.user') // link for User
            );

            return $request;
        });
    }

    /**
     * Reject a request (GA/Admin).
     */
    public function rejectRequest(int $requestId, string $reason, int $actorId): AtkRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $actorId) {
            $request = AtkRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'diajukan') {
                throw new \Exception('Hanya permintaan baru (Diajukan) yang dapat ditolak.');
            }

            $request->update([
                'status' => 'ditolak',
                'rejected_by' => $actorId,
                'rejected_at' => Carbon::now('Asia/Jakarta'),
                'rejection_reason' => $reason,
            ]);

            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'ditolak',
                'notes' => "Permintaan ditolak dengan alasan: {$reason}",
                'actor_id' => $actorId,
            ]);

            // Notify Requester
            Notification::send(
                $request->user_id,
                'Permintaan ATK Ditolak',
                "Permintaan ATK {$request->request_number} ditolak. Alasan: {$reason}",
                route('atk.requests.user')
            );

            return $request;
        });
    }

    /**
     * Process a request and deduct stock (GA/Admin).
     * Transaksi dilindungi dengan row-level lock dan verifikasi ulang stok.
     */
    public function processRequest(int $requestId, int $actorId): AtkRequest
    {
        return DB::transaction(function () use ($requestId, $actorId) {
            // Lock request
            $request = AtkRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'disetujui') {
                throw new \Exception('Permintaan harus berstatus Disetujui sebelum diproses.');
            }

            // Step 1: Re-validate current stock for all items inside database transaction
            foreach ($request->items as $item) {
                // Lock variant
                $variant = AtkVariant::lockForUpdate()->findOrFail($item->variant_id);

                if ($variant->stock < $item->quantity) {
                    throw new \Exception("Stok untuk varian {$variant->name} tidak mencukupi. Tersedia: {$variant->stock} {$variant->unit}, Diminta: {$item->quantity} {$variant->unit}.");
                }
            }

            // Step 2: If all stocks are valid, deduct stock and write movements
            foreach ($request->items as $item) {
                $variant = AtkVariant::find($item->variant_id); // Already locked in step 1
                $previousStock = $variant->stock;
                
                $variant->stock -= $item->quantity;
                $variant->save();

                // Log movement
                AtkStockMovement::create([
                    'variant_id' => $variant->id,
                    'type' => 'request',
                    'reference_id' => $item->id,
                    'reference_type' => AtkRequestItem::class,
                    'previous_stock' => $previousStock,
                    'quantity' => -$item->quantity,
                    'new_stock' => $variant->stock,
                    'actor_id' => $actorId,
                ]);
            }

            // Update status request
            $request->update([
                'status' => 'diproses',
                'processed_by' => $actorId,
                'processed_at' => Carbon::now('Asia/Jakarta'),
            ]);

            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'diproses',
                'notes' => 'Barang sedang diproses untuk penyerahan.',
                'actor_id' => $actorId,
            ]);

            // Notify Requester
            Notification::send(
                $request->user_id,
                'Permintaan ATK Sedang Diproses',
                "Barang pada permintaan {$request->request_number} sedang diproses/diserahkan.",
                route('atk.requests.user')
            );

            return $request;
        });
    }

    /**
     * Complete a request (GA/Admin).
     */
    public function completeRequest(int $requestId, int $actorId): AtkRequest
    {
        return DB::transaction(function () use ($requestId, $actorId) {
            $request = AtkRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'diproses') {
                throw new \Exception('Permintaan harus berstatus Diproses sebelum diselesaikan.');
            }

            $request->update([
                'status' => 'selesai',
                'completed_by' => $actorId,
                'completed_at' => Carbon::now('Asia/Jakarta'),
            ]);

            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'selesai',
                'notes' => 'Permintaan ATK telah selesai diserahkan.',
                'actor_id' => $actorId,
            ]);

            // Record Cost Center transactions for each item in this request
            app(\App\Services\CostCenterTransactionService::class)->recordAtkRequest($request);

            // Notify Requester
            Notification::send(
                $request->user_id,
                'Permintaan ATK Selesai',
                "Permintaan ATK {$request->request_number} telah diselesaikan. Terima kasih.",
                route('atk.requests.user')
            );

            return $request;
        });
    }

    /**
     * Cancel a request (User).
     */
    public function cancelRequest(int $requestId, string $reason, int $actorId): AtkRequest
    {
        return DB::transaction(function () use ($requestId, $reason, $actorId) {
            $request = AtkRequest::lockForUpdate()->findOrFail($requestId);

            if ($request->status !== 'diajukan') {
                throw new \Exception('Permintaan hanya dapat dibatalkan jika belum disetujui.');
            }

            $request->update([
                'status' => 'dibatalkan',
                'cancelled_by' => $actorId,
                'cancelled_at' => Carbon::now('Asia/Jakarta'),
                'cancellation_reason' => $reason,
            ]);

            AtkRequestHistory::create([
                'request_id' => $request->id,
                'status' => 'dibatalkan',
                'notes' => "Permintaan dibatalkan oleh pemohon. Alasan: {$reason}",
                'actor_id' => $actorId,
            ]);

            // Notify GA
            $gaUsers = User::where('role', 'ga')->get();
            foreach ($gaUsers as $ga) {
                Notification::send(
                    $ga->id,
                    'Permintaan ATK Dibatalkan',
                    "Permintaan ATK {$request->request_number} telah dibatalkan oleh pemohon.",
                    route('atk.requests.show', $request->id)
                );
            }

            return $request;
        });
    }

    /**
     * Generate unique Request Number (REQ-ATK-YYYYMMDD-XXXX).
     */
    private function generateRequestNumber(): string
    {
        $today = Carbon::today()->format('Ymd');
        
        $count = AtkRequest::whereDate('created_at', Carbon::today())->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        
        return "REQ-ATK-{$today}-{$sequence}";
    }
}
