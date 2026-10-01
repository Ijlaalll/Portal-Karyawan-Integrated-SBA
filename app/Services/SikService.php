<?php

namespace App\Services;

use App\Models\ExitPermit;
use App\Models\Notification;
use App\Models\SikApprovalHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SikService
{
    public function __construct(
        protected DepartmentApprovalService $departmentApprovalService
    ) {}

    /**
     * Generate safe, unique SIK number (SIK-YYYYMM-XXXXX) with atomic locking.
     */
    public function generateSikNumber(): string
    {
        $prefix = 'SIK-' . Carbon::now('Asia/Jakarta')->format('Ym') . '-';
        
        $latest = ExitPermit::where('sik_number', 'like', "{$prefix}%")
            ->orderBy('sik_number', 'desc')
            ->lockForUpdate()
            ->first();

        if (!$latest) {
            $nextSequence = 1;
        } else {
            $lastNumber = (int) substr($latest->sik_number, -5);
            $nextSequence = $lastNumber + 1;
        }

        return $prefix . str_pad((string)$nextSequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new Exit Permit (Draft or Pending Approval).
     *
     * @throws ValidationException
     */
    public function createPermit(User $user, array $data, bool $submitImmediately = false): ExitPermit
    {
        return DB::transaction(function () use ($user, $data, $submitImmediately) {
            $sikNumber = $this->generateSikNumber();

            // Validate departure & return times
            $this->validateTimes($data['permit_date'], $data['expected_departure_time'], $data['expected_return_time']);

            $approverType = $data['approver_type'] ?? 'default';
            $replacementApproverId = $data['replacement_approver_id'] ?? null;

            $approver = null;
            $isReplacement = false;

            if ($approverType === 'replacement' && !empty($replacementApproverId)) {
                // Anti self-approval check
                if ((int)$replacementApproverId === $user->id) {
                    throw ValidationException::withMessages([
                        'replacement_approver_id' => 'Pemohon tidak boleh memilih dirinya sendiri sebagai approver SIK.',
                    ]);
                }

                $config = $this->departmentApprovalService->getDepartmentConfig($user->department);
                if (!$config || !$config->replacementApprovers()->where('users.id', $replacementApproverId)->exists()) {
                    throw ValidationException::withMessages([
                        'replacement_approver_id' => 'Approver pengganti yang dipilih tidak terdaftar sebagai approver resmi untuk departemen ini.',
                    ]);
                }

                $approver = User::where('id', $replacementApproverId)->where('status', 'active')->firstOrFail();
                $isReplacement = true;
            } else {
                $approver = $this->departmentApprovalService->getApproverForApplicant($user);
                if ($approver->id === $user->id) {
                    throw ValidationException::withMessages([
                        'approver' => 'Pemohon tidak boleh menjadi approver untuk pengajuan SIK miliknya sendiri.',
                    ]);
                }
                $isReplacement = false;
            }

            $status = $submitImmediately ? ExitPermit::STATUS_PENDING_APPROVAL : ExitPermit::STATUS_DRAFT;

            $permit = ExitPermit::create([
                'sik_number' => $sikNumber,
                'user_id' => $user->id,
                'department' => $user->department ?? '-',
                'position' => $user->position,
                'employee_id' => $user->employee_id,
                'purpose_type' => $data['purpose_type'] ?? 'dinas',
                'purpose' => $data['purpose'],
                'purpose_detail' => $data['purpose_detail'] ?? null,
                'permit_date' => $data['permit_date'],
                'expected_departure_time' => $data['expected_departure_time'],
                'expected_return_time' => $data['expected_return_time'],
                'vehicle_type' => $data['vehicle_type'] ?? 'none',
                'vehicle_details' => $data['vehicle_details'] ?? null,
                'status' => $status,
                'approver_id' => $approver ? $approver->id : null,
                'is_replacement_approver' => $isReplacement,
            ]);

            if ($submitImmediately && $approver) {
                Notification::send(
                    $approver->id,
                    'Pengajuan SIK Baru Menunggu Persetujuan',
                    "Pengajuan SIK #{$permit->sik_number} dari {$user->name} ({$permit->department}) menunggu persetujuan Anda.",
                    route('sik.approvals.index')
                );

                $emailService = app(\App\Services\EmailService::class);
                $emailService->sendSikSubmittedToApprover($permit, $approver);
                $emailService->sendSikSubmittedToRequester($permit);
            }

            return $permit;
        });
    }

    /**
     * Update an existing draft Exit Permit.
     *
     * @throws ValidationException
     */
    public function updateDraft(ExitPermit $sik, User $user, array $data, bool $submitImmediately = false): ExitPermit
    {
        return DB::transaction(function () use ($sik, $user, $data, $submitImmediately) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if ($sik->status !== ExitPermit::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pengajuan SIK berstatus DRAFT yang dapat diedit.',
                ]);
            }

            $this->validateTimes($data['permit_date'], $data['expected_departure_time'], $data['expected_return_time']);

            $approverType = $data['approver_type'] ?? ($sik->is_replacement_approver ? 'replacement' : 'default');
            $replacementApproverId = $data['replacement_approver_id'] ?? null;

            $approver = null;
            $isReplacement = false;

            if ($approverType === 'replacement' && !empty($replacementApproverId)) {
                if ((int)$replacementApproverId === $user->id) {
                    throw ValidationException::withMessages([
                        'replacement_approver_id' => 'Pemohon tidak boleh memilih dirinya sendiri sebagai approver SIK.',
                    ]);
                }

                $config = $this->departmentApprovalService->getDepartmentConfig($user->department);
                if (!$config || !$config->replacementApprovers()->where('users.id', $replacementApproverId)->exists()) {
                    throw ValidationException::withMessages([
                        'replacement_approver_id' => 'Approver pengganti yang dipilih tidak terdaftar sebagai approver resmi untuk departemen ini.',
                    ]);
                }

                $approver = User::where('id', $replacementApproverId)->where('status', 'active')->firstOrFail();
                $isReplacement = true;
            } else {
                $approver = $this->departmentApprovalService->getApproverForApplicant($user);
                if ($approver->id === $user->id) {
                    throw ValidationException::withMessages([
                        'approver' => 'Pemohon tidak boleh menjadi approver untuk pengajuan SIK miliknya sendiri.',
                    ]);
                }
                $isReplacement = false;
            }

            $status = $submitImmediately ? ExitPermit::STATUS_PENDING_APPROVAL : ExitPermit::STATUS_DRAFT;

            $sik->update([
                'department' => $user->department ?? '-',
                'position' => $user->position,
                'employee_id' => $user->employee_id,
                'purpose_type' => $data['purpose_type'] ?? 'dinas',
                'purpose' => $data['purpose'],
                'purpose_detail' => $data['purpose_detail'] ?? null,
                'permit_date' => $data['permit_date'],
                'expected_departure_time' => $data['expected_departure_time'],
                'expected_return_time' => $data['expected_return_time'],
                'vehicle_type' => $data['vehicle_type'] ?? 'none',
                'vehicle_details' => $data['vehicle_details'] ?? null,
                'status' => $status,
                'approver_id' => $approver ? $approver->id : null,
                'is_replacement_approver' => $isReplacement,
            ]);

            if ($submitImmediately && $approver) {
                Notification::send(
                    $approver->id,
                    'Pengajuan SIK Baru Menunggu Persetujuan',
                    "Pengajuan SIK #{$sik->sik_number} dari {$user->name} ({$sik->department}) menunggu persetujuan Anda.",
                    route('sik.approvals.index')
                );

                $emailService = app(\App\Services\EmailService::class);
                $emailService->sendSikSubmittedToApprover($sik, $approver);
                $emailService->sendSikSubmittedToRequester($sik);
            }

            return $sik;
        });
    }

    /**
     * Submit a draft Exit Permit for approval.
     *
     * @throws ValidationException
     */
    public function submitDraft(ExitPermit $sik, User $user): ExitPermit
    {
        return DB::transaction(function () use ($sik, $user) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if ($sik->status !== ExitPermit::STATUS_DRAFT) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan SIK ini sudah pernah diajukan atau tidak dalam status DRAFT.',
                ]);
            }

            if ($sik->approver_id) {
                $approver = User::findOrFail($sik->approver_id);
            } else {
                $approver = $this->departmentApprovalService->getApproverForApplicant($user);
                $sik->approver_id = $approver->id;
                $sik->is_replacement_approver = false;
            }

            if ($approver->id === $user->id) {
                throw ValidationException::withMessages([
                    'approver' => 'Pemohon tidak boleh menjadi approver untuk pengajuan SIK miliknya sendiri.',
                ]);
            }

            $sik->update([
                'status' => ExitPermit::STATUS_PENDING_APPROVAL,
                'approver_id' => $approver->id,
                'is_replacement_approver' => $sik->is_replacement_approver,
            ]);

            Notification::send(
                $approver->id,
                'Pengajuan SIK Baru Menunggu Persetujuan',
                "Pengajuan SIK #{$sik->sik_number} dari {$user->name} ({$sik->department}) menunggu persetujuan Anda.",
                route('sik.approvals.index')
            );

            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendSikSubmittedToApprover($sik, $approver);
            $emailService->sendSikSubmittedToRequester($sik);

            return $sik;
        });
    }

    /**
     * Approve an Exit Permit.
     *
     * @throws ValidationException
     */
    public function approve(ExitPermit $sik, User $approver, ?string $note = null): ExitPermit
    {
        return DB::transaction(function () use ($sik, $approver, $note) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if ($sik->status !== ExitPermit::STATUS_PENDING_APPROVAL) {
                throw ValidationException::withMessages([
                    'status' => 'SIK tidak dapat disetujui karena status saat ini bukan PENDING_APPROVAL.',
                ]);
            }

            if ($sik->user_id === $approver->id) {
                throw ValidationException::withMessages([
                    'approver' => 'Anda tidak diizinkan menyetujui pengajuan SIK milik Anda sendiri.',
                ]);
            }

            // Cryptographically secure random token (40 chars)
            $qrToken = 'SIK-' . bin2hex(random_bytes(18));

            $sik->update([
                'status' => ExitPermit::STATUS_APPROVED,
                'approved_at' => Carbon::now('Asia/Jakarta'),
                'approval_note' => $note,
                'qr_token' => $qrToken,
            ]);

            SikApprovalHistory::create([
                'exit_permit_id' => $sik->id,
                'approver_id' => $approver->id,
                'approver_type' => $sik->is_replacement_approver ? 'replacement' : 'default',
                'status' => 'APPROVED',
                'note' => $note,
                'actioned_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Notification::send(
                $sik->user_id,
                'SIK Disetujui',
                "Pengajuan SIK #{$sik->sik_number} Anda telah disetujui. QR Code izin keluar siap digunakan.",
                route('sik.show', $sik)
            );

            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendSikApproved($sik);

            // Notify active security officers
            $securityUsers = User::where('role', 'security')->where('status', 'active')->get();
            foreach ($securityUsers as $sec) {
                $emailService->sendSikApprovedToSecurity($sik, $sec->email);
            }

            return $sik;
        });
    }

    /**
     * Reject an Exit Permit.
     *
     * @throws ValidationException
     */
    public function reject(ExitPermit $sik, User $approver, string $reason): ExitPermit
    {
        return DB::transaction(function () use ($sik, $approver, $reason) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if ($sik->status !== ExitPermit::STATUS_PENDING_APPROVAL) {
                throw ValidationException::withMessages([
                    'status' => 'SIK tidak dapat ditolak karena status saat ini bukan PENDING_APPROVAL.',
                ]);
            }

            if ($sik->user_id === $approver->id) {
                throw ValidationException::withMessages([
                    'approver' => 'Anda tidak diizinkan menolak pengajuan SIK milik Anda sendiri.',
                ]);
            }

            if (empty(trim($reason))) {
                throw ValidationException::withMessages([
                    'rejection_reason' => 'Alasan penolakan wajib diisi.',
                ]);
            }

            $sik->update([
                'status' => ExitPermit::STATUS_REJECTED,
                'rejection_reason' => trim($reason),
            ]);

            SikApprovalHistory::create([
                'exit_permit_id' => $sik->id,
                'approver_id' => $approver->id,
                'approver_type' => $sik->is_replacement_approver ? 'replacement' : 'default',
                'status' => 'REJECTED',
                'note' => trim($reason),
                'actioned_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Notification::send(
                $sik->user_id,
                'SIK Ditolak',
                "Pengajuan SIK #{$sik->sik_number} Anda ditolak. Alasan: " . trim($reason),
                route('sik.show', $sik)
            );

            app(\App\Services\EmailService::class)->sendSikRejected($sik, trim($reason));

            return $sik;
        });
    }

    /**
     * Revoke an approved Exit Permit before departure.
     *
     * @throws ValidationException
     */
    public function revoke(ExitPermit $sik, User $revoker, string $reason): ExitPermit
    {
        return DB::transaction(function () use ($sik, $revoker, $reason) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if ($sik->status !== ExitPermit::STATUS_APPROVED || $sik->actual_departure_at !== null) {
                throw ValidationException::withMessages([
                    'status' => 'Pencabutan (REVOKE) hanya dapat dilakukan pada SIK yang sudah disetujui dan belum digunakan untuk keluar.',
                ]);
            }

            if (empty(trim($reason))) {
                throw ValidationException::withMessages([
                    'revoke_reason' => 'Alasan pencabutan izin wajib diisi.',
                ]);
            }

            $sik->update([
                'status' => ExitPermit::STATUS_REVOKED,
                'revoked_by' => $revoker->id,
                'revoked_at' => Carbon::now('Asia/Jakarta'),
                'revoke_reason' => trim($reason),
            ]);

            SikApprovalHistory::create([
                'exit_permit_id' => $sik->id,
                'approver_id' => $revoker->id,
                'approver_type' => $sik->is_replacement_approver ? 'replacement' : 'default',
                'status' => 'REVOKED',
                'note' => trim($reason),
                'actioned_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Notification::send(
                $sik->user_id,
                'SIK Dicabut (Revoked)',
                "SIK #{$sik->sik_number} telah dicabut oleh {$revoker->name}. Alasan: " . trim($reason),
                route('sik.show', $sik)
            );

            return $sik;
        });
    }

    /**
     * Close an outside/overdue Exit Permit manually (emergency case where applicant cannot return).
     *
     * @throws ValidationException
     */
    public function closeManually(ExitPermit $sik, User $closer, string $reason): ExitPermit
    {
        return DB::transaction(function () use ($sik, $closer, $reason) {
            $sik = ExitPermit::where('id', $sik->id)->lockForUpdate()->firstOrFail();

            if (!in_array($sik->status, [ExitPermit::STATUS_OUTSIDE, ExitPermit::STATUS_OVERDUE], true) && $sik->effective_status !== ExitPermit::STATUS_OVERDUE) {
                throw ValidationException::withMessages([
                    'status' => 'Tutup Manual hanya dapat dilakukan pada SIK yang sedang berada di luar (OUTSIDE / OVERDUE).',
                ]);
            }

            if ($sik->actual_return_at !== null) {
                throw ValidationException::withMessages([
                    'status' => 'SIK sudah tercatat kembali secara faktual.',
                ]);
            }

            if (empty(trim($reason))) {
                throw ValidationException::withMessages([
                    'close_reason' => 'Alasan penutupan manual wajib diisi.',
                ]);
            }

            $sik->update([
                'status' => ExitPermit::STATUS_CLOSED_MANUALLY,
                'closed_by' => $closer->id,
                'closed_at' => Carbon::now('Asia/Jakarta'),
                'close_reason' => trim($reason),
            ]);

            SikApprovalHistory::create([
                'exit_permit_id' => $sik->id,
                'approver_id' => $closer->id,
                'approver_type' => $sik->is_replacement_approver ? 'replacement' : 'default',
                'status' => 'CLOSED_MANUALLY',
                'note' => trim($reason),
                'actioned_at' => Carbon::now('Asia/Jakarta'),
            ]);

            Notification::send(
                $sik->user_id,
                'SIK Ditutup Secara Manual',
                "SIK #{$sik->sik_number} telah ditutup secara administratif oleh {$closer->name}. Catatan: " . trim($reason),
                route('sik.show', $sik)
            );

            return $sik;
        });
    }

    /**
     * Cancel draft or pending approval permit by applicant.
     *
     * @throws ValidationException
     */
    public function cancel(ExitPermit $sik, User $user): void
    {
        if ($sik->user_id !== $user->id && !$user->isAdmin()) {
            throw ValidationException::withMessages([
                'authorization' => 'Anda tidak memiliki izin membatalkan pengajuan ini.',
            ]);
        }

        if (!in_array($sik->status, [ExitPermit::STATUS_DRAFT, ExitPermit::STATUS_PENDING_APPROVAL], true)) {
            throw ValidationException::withMessages([
                'status' => 'SIK yang sudah disetujui tidak dapat dibatalkan. Hubungi pihak berwenang untuk pencabutan (REVOKE).',
            ]);
        }

        $sik->delete();
    }

    /**
     * Helper to validate departure and return times.
     *
     * @throws ValidationException
     */
    protected function validateTimes(string $permitDate, string $departureTime, string $returnTime): void
    {
        $dep = substr($departureTime, 0, 5);
        $ret = substr($returnTime, 0, 5);

        if ($ret <= $dep) {
            throw ValidationException::withMessages([
                'expected_return_time' => 'Waktu rencana kembali harus lebih besar dari waktu rencana keluar.',
            ]);
        }
    }
}
