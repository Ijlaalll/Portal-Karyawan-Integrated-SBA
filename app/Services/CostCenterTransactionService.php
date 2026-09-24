<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\AtkRequest;
use App\Models\CostCenterTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostCenterTransactionService
{
    /**
     * Record a Cost Center transaction for an approved/completed room booking.
     * Guaranteed idempotent and validates booking status and room pricing.
     */
    public function recordRoomBooking(Booking $booking): ?CostCenterTransaction
    {
        // 1. Must be in a final confirmed status
        if (!in_array($booking->status, ['approved', 'completed'])) {
            return null;
        }

        // 2. Online meetings without physical room do not incur room rates
        if (!$booking->room_id) {
            return null;
        }

        $booking->loadMissing(['room', 'user']);

        // 3. User must have an associated Cost Center
        if (!$booking->user || !$booking->user->cost_center_id) {
            return null;
        }

        // 4. Room pricing must be active and configured (> 0)
        $room = $booking->room;
        if (!$room || !$room->isPricingActive() || (float)$room->price_per_hour <= 0) {
            return null;
        }

        // 5. Calculate booking duration in hours
        try {
            $start = Carbon::parse($booking->start_time);
            $end = Carbon::parse($booking->end_time);
            $durationMinutes = $start->diffInMinutes($end);
            $durationHours = round($durationMinutes / 60, 2);
        } catch (\Exception $e) {
            Log::warning("Could not calculate duration for booking ID {$booking->id}: {$e->getMessage()}");
            return null;
        }

        if ($durationHours <= 0) {
            return null;
        }

        $unitPrice = (float) $room->price_per_hour;
        $totalAmount = round($durationHours * $unitPrice, 2);

        // 6. Idempotency Check: Return existing transaction if already posted
        $existing = CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
            ->where('reference_id', $booking->id)
            ->where('reference_item_id', 0)
            ->where('status', 'posted')
            ->first();

        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($booking, $room, $durationHours, $unitPrice, $totalAmount) {
                return CostCenterTransaction::create([
                    'cost_center_id'    => $booking->user->cost_center_id,
                    'user_id'           => $booking->user_id,
                    'activity_type'     => 'ROOM_BOOKING',
                    'reference_id'      => $booking->id,
                    'reference_item_id' => 0,
                    'description'       => "Reservasi Ruang {$room->name} ({$booking->title})",
                    'item_name'         => $room->name,
                    'quantity'          => $durationHours,
                    'unit_price'        => $unitPrice,
                    'total_amount'      => $totalAmount,
                    'status'            => 'posted',
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Concurrency protection: return existing
            return CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
                ->where('reference_id', $booking->id)
                ->where('reference_item_id', 0)
                ->where('status', 'posted')
                ->first();
        }
    }

    /**
     * Reverse a room booking transaction upon cancellation.
     * Guaranteed idempotent and safe to run multiple times (e.g. in sync routines).
     */
    public function reverseRoomBooking(Booking $booking): ?CostCenterTransaction
    {
        // 1. Locate the original posted transaction
        $posted = CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
            ->where('reference_id', $booking->id)
            ->where('reference_item_id', 0)
            ->where('status', 'posted')
            ->first();

        if (!$posted) {
            return null;
        }

        // 2. Check if reversal already exists
        $existingReversal = CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
            ->where('reference_id', $booking->id)
            ->where('reference_item_id', 0)
            ->where('status', 'reversed')
            ->first();

        if ($existingReversal) {
            return $existingReversal;
        }

        try {
            return DB::transaction(function () use ($booking, $posted) {
                return CostCenterTransaction::create([
                    'cost_center_id'    => $posted->cost_center_id,
                    'user_id'           => $posted->user_id,
                    'activity_type'     => 'ROOM_BOOKING',
                    'reference_id'      => $booking->id,
                    'reference_item_id' => 0,
                    'description'       => "Pembatalan: " . $posted->description,
                    'item_name'         => $posted->item_name,
                    'quantity'          => $posted->quantity,
                    'unit_price'        => $posted->unit_price,
                    'total_amount'      => -1 * abs((float)$posted->total_amount),
                    'status'            => 'reversed',
                ]);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return CostCenterTransaction::where('activity_type', 'ROOM_BOOKING')
                ->where('reference_id', $booking->id)
                ->where('reference_item_id', 0)
                ->where('status', 'reversed')
                ->first();
        }
    }

    /**
     * Record Cost Center transactions for completed ATK requests.
     * Generates one transaction per request item preserving historical unit price snapshots.
     */
    public function recordAtkRequest(AtkRequest $request): array
    {
        if ($request->status !== 'selesai') {
            return [];
        }

        $request->loadMissing(['items.variant.item', 'user']);

        if (!$request->user || !$request->user->cost_center_id) {
            return [];
        }

        $created = [];

        foreach ($request->items as $item) {
            $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->variant->purchase_price ?? 0));
            $subtotal = (float) ($item->subtotal > 0 ? $item->subtotal : ($item->quantity * $unitPrice));

            if ($subtotal <= 0 || $item->quantity <= 0) {
                continue;
            }

            // Check if transaction already exists for this request item
            $existing = CostCenterTransaction::where('activity_type', 'ATK_USAGE')
                ->where('reference_id', $request->id)
                ->where('reference_item_id', $item->id)
                ->where('status', 'posted')
                ->first();

            if ($existing) {
                $created[] = $existing;
                continue;
            }

            $variantName = $item->variant 
                ? ($item->variant->item->name . ' - ' . $item->variant->name)
                : "Item #{$item->id}";

            try {
                $tx = CostCenterTransaction::create([
                    'cost_center_id'    => $request->user->cost_center_id,
                    'user_id'           => $request->user_id,
                    'activity_type'     => 'ATK_USAGE',
                    'reference_id'      => $request->id,
                    'reference_item_id' => $item->id,
                    'description'       => "Penggunaan ATK: {$variantName} ({$request->request_number})",
                    'item_name'         => $variantName,
                    'quantity'          => $item->quantity,
                    'unit_price'        => $unitPrice,
                    'total_amount'      => $subtotal,
                    'status'            => 'posted',
                ]);
                $created[] = $tx;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $created[] = CostCenterTransaction::where('activity_type', 'ATK_USAGE')
                    ->where('reference_id', $request->id)
                    ->where('reference_item_id', $item->id)
                    ->where('status', 'posted')
                    ->first();
            }
        }

        return $created;
    }
}
