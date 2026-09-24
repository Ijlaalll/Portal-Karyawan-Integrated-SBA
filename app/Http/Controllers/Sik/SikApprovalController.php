<?php

namespace App\Http\Controllers\Sik;

use App\Http\Controllers\Controller;
use App\Models\ExitPermit;
use App\Services\DepartmentApprovalService;
use App\Services\SikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SikApprovalController extends Controller
{
    public function __construct(
        protected SikService $sikService,
        protected DepartmentApprovalService $departmentApprovalService
    ) {}

    /**
     * Display list of SIKs waiting for approval.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isApprover = $this->departmentApprovalService->isUserAuthorizedApprover($user);

        if (!$isApprover) {
            abort(403, 'Akses ditolak: Anda tidak memiliki kewenangan sebagai Approver untuk menyetujui pengajuan SIK.');
        }

        $query = ExitPermit::where('status', ExitPermit::STATUS_PENDING_APPROVAL)
            ->where('user_id', '!=', $user->id) // Anti self-approval!
            ->with(['user', 'approver'])
            ->orderBy('created_at', 'desc');

        // Snapshot authoritative: non-admin approver only sees SIKs assigned directly to them
        if (!$user->isAdmin()) {
            $query->where('approver_id', $user->id);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sik_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Department Filter
        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        $pendingPermits = $query->paginate(10)->withQueryString();

        return view('sik.approvals.index', compact('pendingPermits'));
    }

    /**
     * Display review page for a specific pending SIK.
     */
    public function show(ExitPermit $sik)
    {
        Gate::authorize('view', $sik);

        $sik->load(['user', 'approver', 'approvalHistories.approver']);

        return view('sik.approvals.show', compact('sik'));
    }

    /**
     * Approve a SIK.
     */
    public function approve(Request $request, ExitPermit $sik)
    {
        Gate::authorize('approve', $sik);

        $request->validate([
            'approval_note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->sikService->approve($sik, $request->user(), $request->input('approval_note'));

        return redirect()->route('sik.approvals.index')
            ->with('success', "SIK #{$sik->sik_number} berhasil disetujui. QR Code telah diterbitkan untuk pemohon.");
    }

    /**
     * Reject a SIK.
     */
    public function reject(Request $request, ExitPermit $sik)
    {
        Gate::authorize('reject', $sik);

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan pengajuan SIK wajib diisi.',
        ]);

        $this->sikService->reject($sik, $request->user(), $request->input('rejection_reason'));

        return redirect()->route('sik.approvals.index')
            ->with('success', "SIK #{$sik->sik_number} telah ditolak.");
    }

    /**
     * Revoke an approved SIK before departure.
     */
    public function revoke(Request $request, ExitPermit $sik)
    {
        Gate::authorize('revoke', $sik);

        $request->validate([
            'revoke_reason' => ['required', 'string', 'max:1000'],
        ], [
            'revoke_reason.required' => 'Alasan pencabutan izin SIK wajib diisi.',
        ]);

        $this->sikService->revoke($sik, $request->user(), $request->input('revoke_reason'));

        return redirect()->back()
            ->with('success', "SIK #{$sik->sik_number} berhasil dicabut (REVOKED). QR Code sudah tidak berlaku.");
    }

    /**
     * Close an outside/overdue SIK manually (emergency/unable to return).
     */
    public function closeManually(Request $request, ExitPermit $sik)
    {
        Gate::authorize('closeManually', $sik);

        $request->validate([
            'close_reason' => ['required', 'string', 'max:1000'],
        ], [
            'close_reason.required' => 'Alasan penutupan manual wajib diisi.',
        ]);

        $this->sikService->closeManually($sik, $request->user(), $request->input('close_reason'));

        return redirect()->back()
            ->with('success', "SIK #{$sik->sik_number} berhasil ditutup secara administratif (CLOSED_MANUALLY).");
    }
}
