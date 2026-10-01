<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleRequest;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class VehicleRequestService
{
    /**
     * Check if a specific vehicle is available for the given timeframe.
     */
    public function isVehicleAvailable(int $vehicleId, string $startDatetime, string $endDatetime, ?int $excludeRequestId = null): bool
    {
        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle || $vehicle->status !== 'available') {
            return false;
        }

        $query = VehicleRequest::where('vehicle_id', $vehicleId)
            ->where('status', 'approved')
            ->where('start_datetime', '<', $endDatetime)
            ->where('end_datetime', '>', $startDatetime);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return !$query->exists();
    }

    /**
     * Check if a specific driver is available for the given timeframe.
     */
    public function isDriverAvailable(int $driverId, string $startDatetime, string $endDatetime, ?int $excludeRequestId = null): bool
    {
        $driver = Driver::find($driverId);
        if (!$driver || $driver->status !== 'available') {
            return false;
        }

        $query = VehicleRequest::where('driver_id', $driverId)
            ->where('status', 'approved')
            ->where('start_datetime', '<', $endDatetime)
            ->where('end_datetime', '>', $startDatetime);

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return !$query->exists();
    }

    /**
     * Create a new vehicle request.
     */
    public function createRequest(User $user, array $data): VehicleRequest
    {
        return DB::transaction(function () use ($user, $data) {
            // Generate request number
            $requestNumber = $this->generateRequestNumber();

            // Vehicle choice in request creation is strictly an optional preference;
            // final assignment will be decided by GA during operational approval.
            $request = VehicleRequest::create([
                'request_number' => $requestNumber,
                'user_id'        => $user->id,
                'purpose'        => $data['purpose'],
                'destination'    => $data['destination'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime'   => $data['end_datetime'],
                'vehicle_id'     => $data['vehicle_id'] ?? null,
                'driver_id'      => null,
                'status'         => 'pending_approval',
                'notes'          => $data['notes'] ?? null,
            ]);

            // Notify GA as the confirmed operational approval authority
            $gaUsers = User::where('role', 'ga')->where('status', 'active')->get();
            $emailService = app(\App\Services\EmailService::class);

            foreach ($gaUsers as $ga) {
                Notification::send(
                    $ga->id,
                    'Permohonan Kendaraan Baru',
                    "Permohonan #{$requestNumber} dari {$user->name} menunggu persetujuan dan penugasan armada.",
                    route('vehicles.requests.show', $request->id)
                );
                $emailService->sendVehicleRequestSubmittedToGa($request, $ga->email);
            }

            // Notify requester confirmation email
            $emailService->sendVehicleRequestSubmittedToRequester($request);

            return $request;
        });
    }

    /**
     * Cancel an existing request (by owner).
     */
    public function cancelRequest(VehicleRequest $request, User $user): VehicleRequest
    {
        if ($request->user_id !== $user->id && !$user->isAdmin()) {
            throw new Exception('Anda tidak berhak membatalkan permohonan ini.');
        }

        if ($request->status !== 'pending_approval') {
            throw new Exception('Permohonan hanya dapat dibatalkan saat masih berstatus Menunggu Persetujuan.');
        }

        $request->update([
            'status' => 'cancelled',
        ]);

        return $request;
    }

    /**
     * Approve request and assign final vehicle & driver (Operational Approval by GA).
     * Supports both assigned driver and self-drive / lepas kunci (driverId = null).
     */
    public function approveRequest(VehicleRequest $request, User $approver, int $vehicleId, ?int $driverId = null): VehicleRequest
    {
        if (!$approver->isGa()) {
            throw new Exception('Akses ditolak. Persetujuan operasional kendaraan hanya dapat dilakukan oleh GA.');
        }

        return DB::transaction(function () use ($request, $approver, $vehicleId, $driverId) {
            $req = VehicleRequest::lockForUpdate()->findOrFail($request->id);

            if ($req->status !== 'pending_approval') {
                throw new Exception('Hanya permohonan berstatus Menunggu Persetujuan yang dapat disetujui.');
            }

            if (!$this->isVehicleAvailable($vehicleId, $req->start_datetime, $req->end_datetime, $req->id)) {
                throw new Exception('Kendaraan yang dipilih tidak tersedia atau bentrok dengan jadwal permohonan lain yang telah disetujui.');
            }

            if ($driverId !== null && !$this->isDriverAvailable($driverId, $req->start_datetime, $req->end_datetime, $req->id)) {
                throw new Exception('Supir yang dipilih tidak tersedia atau bentrok dengan jadwal permohonan lain yang telah disetujui.');
            }

            $req->update([
                'vehicle_id'  => $vehicleId,
                'driver_id'   => $driverId,
                'status'      => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => Carbon::now('Asia/Jakarta'),
            ]);

            // Notify requester
            $assignedInfo = [];
            if ($req->vehicle) {
                $assignedInfo[] = "Mobil: {$req->vehicle->name} ({$req->vehicle->plate_number})";
            }
            if ($req->driver) {
                $assignedInfo[] = "Supir: {$req->driver->name}";
            } else {
                $assignedInfo[] = "Supir: Lepas Kunci (Tanpa Supir)";
            }
            $detailMsg = !empty($assignedInfo) ? ' (' . implode(', ', $assignedInfo) . ')' : '';

            Notification::send(
                $req->user_id,
                'Permohonan Kendaraan Disetujui',
                "Permohonan #{$req->request_number} telah disetujui{$detailMsg}.",
                route('vehicles.requests.show', $req->id)
            );

            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendVehicleRequestApprovedToRequester($req);

            if ($driverId !== null) {
                $driver = Driver::with('user')->find($driverId);
                if ($driver) {
                    $emailService->sendVehicleRequestApprovedToDriver($req, $driver);
                }
            }

            return $req;
        });
    }

    /**
     * Reject request with reason (Operational Rejection by GA).
     */
    public function rejectRequest(VehicleRequest $request, User $approver, string $reason): VehicleRequest
    {
        if (!$approver->isGa()) {
            throw new Exception('Akses ditolak. Penolakan operasional kendaraan hanya dapat dilakukan oleh GA.');
        }

        return DB::transaction(function () use ($request, $approver, $reason) {
            $req = VehicleRequest::lockForUpdate()->findOrFail($request->id);

            if ($req->status !== 'pending_approval') {
                throw new Exception('Hanya permohonan berstatus Menunggu Persetujuan yang dapat ditolak.');
            }

            $req->update([
                'status'           => 'rejected',
                'approved_by'      => $approver->id,
                'approved_at'      => Carbon::now('Asia/Jakarta'),
                'rejection_reason' => $reason,
            ]);

            Notification::send(
                $req->user_id,
                'Permohonan Kendaraan Ditolak',
                "Permohonan #{$req->request_number} ditolak. Alasan: {$reason}",
                route('vehicles.requests.show', $req->id)
            );

            app(\App\Services\EmailService::class)->sendVehicleRequestRejectedToRequester($req, $reason);

            return $req;
        });
    }

    /**
     * Mark trip as completed.
     */
    public function completeRequest(VehicleRequest $request): VehicleRequest
    {
        if ($request->status !== 'approved') {
            throw new Exception('Hanya permohonan yang telah disetujui yang dapat diselesaikan.');
        }

        $request->update([
            'status' => 'completed',
        ]);

        return $request;
    }

    /**
     * Automatically complete approved requests whose end_datetime has elapsed.
     */
    public function autoCompleteExpiredRequests(?Carbon $now = null): int
    {
        $currentTime = $now ?? Carbon::now('Asia/Jakarta');

        return DB::transaction(function () use ($currentTime) {
            $expiredRequests = VehicleRequest::where('status', 'approved')
                ->where('end_datetime', '<=', $currentTime)
                ->lockForUpdate()
                ->get();

            $count = 0;
            foreach ($expiredRequests as $request) {
                $request->update(['status' => 'completed']);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Generate unique atomic request number (REQ-VHC-YYYYMM-XXXXX).
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'REQ-VHC-' . Carbon::now('Asia/Jakarta')->format('Ym') . '-';
        $latest = VehicleRequest::where('request_number', 'like', "{$prefix}%")
            ->orderBy('request_number', 'desc')
            ->lockForUpdate()
            ->first();

        if (!$latest) {
            $seq = 1;
        } else {
            $lastNum = (int) substr($latest->request_number, -5);
            $seq = $lastNum + 1;
        }

        return $prefix . str_pad((string)$seq, 5, '0', STR_PAD_LEFT);
    }
}
