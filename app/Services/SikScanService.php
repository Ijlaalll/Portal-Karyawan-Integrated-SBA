<?php

namespace App\Services;

use App\Models\ExitPermit;
use App\Models\SikScanLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SikScanService
{
    /**
     * Validate a scanned QR token against SIK state machine.
     */
    public function validateToken(string $token): array
    {
        $token = trim($token);
        if (empty($token)) {
            return [
                'valid' => false,
                'message' => 'Token QR tidak boleh kosong.',
            ];
        }

        $permit = ExitPermit::with(['user', 'approver', 'exitScannedBy', 'returnScannedBy'])
            ->where('qr_token', $token)
            ->first();

        if (!$permit) {
            return [
                'valid' => false,
                'message' => 'QR Code tidak valid atau SIK tidak ditemukan dalam sistem.',
            ];
        }

        // State Machine Validations
        if ($permit->status === ExitPermit::STATUS_APPROVED) {
            if ($permit->actual_departure_at === null) {
                return [
                    'valid' => true,
                    'action' => 'EXIT',
                    'permit' => $permit,
                    'message' => 'DIIZINKAN KELUAR',
                ];
            }

            return [
                'valid' => false,
                'permit' => $permit,
                'message' => 'Pemohon masih tercatat berada di luar pabrik.',
            ];
        }

        if ($permit->status === ExitPermit::STATUS_OUTSIDE || $permit->effective_status === ExitPermit::STATUS_OVERDUE) {
            if ($permit->actual_return_at === null) {
                $isOverdue = $permit->effective_status === ExitPermit::STATUS_OVERDUE;
                return [
                    'valid' => true,
                    'action' => 'RETURN',
                    'permit' => $permit,
                    'message' => $isOverdue ? 'DIIZINKAN KEMBALI (TERLAMBAT)' : 'DIIZINKAN KEMBALI',
                    'is_overdue' => $isOverdue,
                ];
            }

            return [
                'valid' => false,
                'permit' => $permit,
                'message' => 'Pemohon sudah tercatat kembali ke pabrik sebelumnya.',
            ];
        }

        if ($permit->status === ExitPermit::STATUS_RETURNED) {
            $returnedTime = $permit->actual_return_at ? $permit->actual_return_at->format('d M Y H:i') : '-';
            return [
                'valid' => false,
                'permit' => $permit,
                'message' => "SIK ini sudah selesai dan pemohon telah tercatat kembali pada {$returnedTime}. QR Code sudah tidak berlaku.",
            ];
        }

        if ($permit->status === ExitPermit::STATUS_REVOKED) {
            return [
                'valid' => false,
                'permit' => $permit,
                'message' => 'SIK ini telah dicabut (REVOKED). Alasan: ' . ($permit->revoke_reason ?: '-'),
            ];
        }

        if ($permit->status === ExitPermit::STATUS_REJECTED) {
            return [
                'valid' => false,
                'permit' => $permit,
                'message' => 'SIK ini telah ditolak (REJECTED). Alasan: ' . ($permit->rejection_reason ?: '-'),
            ];
        }

        if ($permit->status === ExitPermit::STATUS_CLOSED_MANUALLY) {
            return [
                'valid' => false,
                'permit' => $permit,
                'message' => 'SIK ini telah ditutup secara administratif (CLOSED_MANUALLY). Alasan: ' . ($permit->close_reason ?: '-'),
            ];
        }

        return [
            'valid' => false,
            'permit' => $permit,
            'message' => 'SIK belum disetujui oleh pihak berwenang.',
        ];
    }

    /**
     * Confirm Exit by Security with DB row locking to prevent race conditions.
     *
     * @throws ValidationException
     */
    public function confirmExit(int $permitId, User $securityUser, ?string $gate = 'Pos Utama / Main Gate', ?string $notes = null): ExitPermit
    {
        return DB::transaction(function () use ($permitId, $securityUser, $gate, $notes) {
            $permit = ExitPermit::where('id', $permitId)->lockForUpdate()->firstOrFail();

            if ($permit->status === ExitPermit::STATUS_OUTSIDE || $permit->actual_departure_at !== null) {
                throw ValidationException::withMessages([
                    'scan' => 'Pemohon sudah tercatat keluar sebelumnya. Konfirmasi keluar ganda ditolak.',
                ]);
            }

            if ($permit->status !== ExitPermit::STATUS_APPROVED) {
                throw ValidationException::withMessages([
                    'scan' => "SIK tidak dapat digunakan untuk keluar karena berstatus: {$permit->status}.",
                ]);
            }

            $now = Carbon::now('Asia/Jakarta');

            $permit->update([
                'status' => ExitPermit::STATUS_OUTSIDE,
                'actual_departure_at' => $now,
                'exit_scanned_by' => $securityUser->id,
            ]);

            SikScanLog::create([
                'exit_permit_id' => $permit->id,
                'scanned_by' => $securityUser->id,
                'scan_type' => 'EXIT',
                'scanned_at' => $now,
                'gate' => $gate ?: 'Pos Utama / Main Gate',
                'notes' => $notes,
            ]);

            return $permit;
        });
    }

    /**
     * Confirm Return by Security with DB row locking to prevent race conditions.
     *
     * @throws ValidationException
     */
    public function confirmReturn(int $permitId, User $securityUser, ?string $gate = 'Pos Utama / Main Gate', ?string $notes = null): ExitPermit
    {
        return DB::transaction(function () use ($permitId, $securityUser, $gate, $notes) {
            $permit = ExitPermit::where('id', $permitId)->lockForUpdate()->firstOrFail();

            if ($permit->status === ExitPermit::STATUS_RETURNED || $permit->actual_return_at !== null) {
                throw ValidationException::withMessages([
                    'scan' => 'Pemohon sudah tercatat kembali sebelumnya. Konfirmasi kembali ganda ditolak.',
                ]);
            }

            if ($permit->status !== ExitPermit::STATUS_OUTSIDE && $permit->effective_status !== ExitPermit::STATUS_OVERDUE) {
                throw ValidationException::withMessages([
                    'scan' => "SIK belum tercatat keluar dari pabrik atau tidak berstatus OUTSIDE. Status saat ini: {$permit->status}.",
                ]);
            }

            $now = Carbon::now('Asia/Jakarta');

            $permit->update([
                'status' => ExitPermit::STATUS_RETURNED,
                'actual_return_at' => $now,
                'return_scanned_by' => $securityUser->id,
            ]);

            SikScanLog::create([
                'exit_permit_id' => $permit->id,
                'scanned_by' => $securityUser->id,
                'scan_type' => 'RETURN',
                'scanned_at' => $now,
                'gate' => $gate ?: 'Pos Utama / Main Gate',
                'notes' => $notes,
            ]);

            return $permit;
        });
    }
}
