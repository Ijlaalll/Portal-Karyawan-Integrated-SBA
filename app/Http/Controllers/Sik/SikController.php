<?php

namespace App\Http\Controllers\Sik;

use App\Http\Controllers\Controller;
use App\Models\ExitPermit;
use App\Services\AuditLogService;
use App\Services\DepartmentApprovalService;
use App\Services\SikService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SikController extends Controller
{
    public function __construct(
        protected SikService $sikService,
        protected DepartmentApprovalService $departmentApprovalService
    ) {}

    /**
     * Display a listing of user's own Exit Permits (SIK Saya).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If System Admin or GA is not an employee, redirect to administrative SIK index
        if (($user->isAdmin() || $user->isGa()) && !$user->isEmployee()) {
            return redirect()->route('admin.sik.index');
        }

        $tab = $request->input('tab', 'all');

        $query = ExitPermit::where('user_id', $user->id)
            ->with(['approver', 'exitScannedBy', 'returnScannedBy'])
            ->orderBy('created_at', 'desc');

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sik_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('purpose_detail', 'like', "%{$search}%");
            });
        }

        // Date Filter
        if ($request->filled('date')) {
            $query->where('permit_date', $request->input('date'));
        }

        // Purpose Type Filter
        if ($request->filled('purpose_type') && in_array($request->input('purpose_type'), ['dinas', 'pribadi'])) {
            $query->where('purpose_type', $request->input('purpose_type'));
        }

        // Tab Filter
        if ($tab === 'draft') {
            $query->where('status', ExitPermit::STATUS_DRAFT);
        } elseif ($tab === 'pending') {
            $query->where('status', ExitPermit::STATUS_PENDING_APPROVAL);
        } elseif ($tab === 'approved') {
            $query->where('status', ExitPermit::STATUS_APPROVED);
        } elseif ($tab === 'outside') {
            $query->where('status', ExitPermit::STATUS_OUTSIDE);
        } elseif ($tab === 'history') {
            $query->whereIn('status', [
                ExitPermit::STATUS_RETURNED,
                ExitPermit::STATUS_CLOSED_MANUALLY,
                ExitPermit::STATUS_REVOKED,
                ExitPermit::STATUS_REJECTED,
            ]);
        }

        $permits = $query->paginate(10)->withQueryString();

        // Calculate summary counts for tabs
        $totalCount = ExitPermit::where('user_id', $user->id)->count();
        $draftCount = ExitPermit::where('user_id', $user->id)->where('status', ExitPermit::STATUS_DRAFT)->count();
        $pendingCount = ExitPermit::where('user_id', $user->id)->where('status', ExitPermit::STATUS_PENDING_APPROVAL)->count();
        $approvedCount = ExitPermit::where('user_id', $user->id)->where('status', ExitPermit::STATUS_APPROVED)->count();
        $outsideCount = ExitPermit::where('user_id', $user->id)->where('status', ExitPermit::STATUS_OUTSIDE)->count();
        $historyCount = ExitPermit::where('user_id', $user->id)->whereIn('status', [
            ExitPermit::STATUS_RETURNED,
            ExitPermit::STATUS_CLOSED_MANUALLY,
            ExitPermit::STATUS_REVOKED,
            ExitPermit::STATUS_REJECTED,
        ])->count();

        return view('sik.index', compact(
            'permits',
            'tab',
            'totalCount',
            'draftCount',
            'pendingCount',
            'approvedCount',
            'outsideCount',
            'historyCount'
        ));
    }

    /**
     * Show the form for creating a new SIK.
     */
    public function create(Request $request)
    {
        $user = $request->user();

        if (!$user->isEmployee()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki identitas karyawan yang lengkap (Nomor Karyawan, Departemen, dan Jabatan) untuk mengajukan SIK.');
        }

        $approvalOptions = $this->departmentApprovalService->getEligibleApproversForApplicant($user);

        return view('sik.create', compact('user', 'approvalOptions'));
    }

    /**
     * Store a newly created SIK in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->isEmployee()) {
            abort(403, 'Akses ditolak: Anda tidak memiliki identitas karyawan yang lengkap (Nomor Karyawan, Departemen, dan Jabatan) untuk mengajukan SIK.');
        }

        $validated = $request->validate([
            'purpose_type' => ['required', 'in:dinas,pribadi'],
            'purpose' => ['required', 'string', 'max:255'],
            'purpose_detail' => ['nullable', 'string', 'max:1000'],
            'permit_date' => ['required', 'date'],
            'expected_departure_time' => ['required', 'string'],
            'expected_return_time' => ['required', 'string'],
            'vehicle_type' => ['nullable', 'string', 'in:none,company_vehicle,private_vehicle'],
            'vehicle_details' => ['nullable', 'string', 'max:255'],
            'approver_type' => ['nullable', 'in:default,replacement'],
            'replacement_approver_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'in:draft,submit'],
        ], [
            'purpose.required' => 'Keperluan keluar wajib diisi.',
            'permit_date.required' => 'Tanggal izin keluar wajib diisi.',
            'expected_departure_time.required' => 'Waktu rencana keluar wajib diisi.',
            'expected_return_time.required' => 'Waktu rencana kembali wajib diisi.',
        ]);

        $submitImmediately = ($validated['action'] === 'submit');

        $permit = $this->sikService->createPermit($user, $validated, $submitImmediately);

        AuditLogService::log(
            'sik',
            'create',
            "Pengguna {$user->name} membuat pengajuan SIK #{$permit->sik_number} untuk keperluan {$permit->purpose} (" . ($submitImmediately ? 'diajukan' : 'draft') . ")",
            ['sik_id' => $permit->id, 'sik_number' => $permit->sik_number, 'purpose' => $permit->purpose],
            $user
        );

        $msg = $submitImmediately
            ? 'Pengajuan SIK berhasil dikirim untuk persetujuan HOD / Atasan.'
            : 'Pengajuan SIK berhasil disimpan sebagai DRAFT.';

        return redirect()->route('sik.show', $permit)->with('success', $msg);
    }

    /**
     * Display the specified SIK.
     */
    public function show(ExitPermit $sik)
    {
        Gate::authorize('view', $sik);

        $sik->load([
            'user',
            'approver',
            'exitScannedBy',
            'returnScannedBy',
            'revokedBy',
            'closedBy',
            'approvalHistories.approver',
            'scanLogs.security',
        ]);

        return view('sik.show', compact('sik'));
    }

    /**
     * Show the form for editing the specified draft SIK.
     */
    public function edit(ExitPermit $sik)
    {
        Gate::authorize('update', $sik);

        $user = auth()->user();
        $approvalOptions = $this->departmentApprovalService->getEligibleApproversForApplicant($sik->user);

        return view('sik.edit', compact('sik', 'user', 'approvalOptions'));
    }

    /**
     * Update the specified draft SIK in storage.
     */
    public function update(Request $request, ExitPermit $sik)
    {
        Gate::authorize('update', $sik);

        $validated = $request->validate([
            'purpose_type' => ['required', 'in:dinas,pribadi'],
            'purpose' => ['required', 'string', 'max:255'],
            'purpose_detail' => ['nullable', 'string', 'max:1000'],
            'permit_date' => ['required', 'date'],
            'expected_departure_time' => ['required', 'string'],
            'expected_return_time' => ['required', 'string'],
            'vehicle_type' => ['nullable', 'string', 'in:none,company_vehicle,private_vehicle'],
            'vehicle_details' => ['nullable', 'string', 'max:255'],
            'approver_type' => ['nullable', 'in:default,replacement'],
            'replacement_approver_id' => ['nullable', 'exists:users,id'],
            'action' => ['required', 'in:draft,submit'],
        ]);

        $submitImmediately = ($validated['action'] === 'submit');

        $this->sikService->updateDraft($sik, $request->user(), $validated, $submitImmediately);

        AuditLogService::log(
            'sik',
            'update',
            "Pengguna {$request->user()->name} memperbarui SIK #{$sik->sik_number}",
            ['sik_id' => $sik->id, 'sik_number' => $sik->sik_number],
            $request->user()
        );

        $msg = $submitImmediately
            ? 'Pengajuan SIK berhasil dikirim untuk persetujuan HOD / Atasan.'
            : 'Perubahan DRAFT SIK berhasil disimpan.';

        return redirect()->route('sik.show', $sik)->with('success', $msg);
    }

    /**
     * Submit a draft SIK directly.
     */
    public function submit(ExitPermit $sik, Request $request)
    {
        Gate::authorize('update', $sik);

        $this->sikService->submitDraft($sik, $request->user());

        AuditLogService::log(
            'sik',
            'submit',
            "Pengguna {$request->user()->name} mengirimkan draf SIK #{$sik->sik_number} untuk persetujuan",
            ['sik_id' => $sik->id, 'sik_number' => $sik->sik_number],
            $request->user()
        );

        return redirect()->route('sik.show', $sik)
            ->with('success', 'Pengajuan SIK berhasil dikirim untuk persetujuan HOD / Atasan.');
    }

    /**
     * Cancel a draft or pending SIK.
     */
    public function cancel(ExitPermit $sik, Request $request)
    {
        Gate::authorize('cancel', $sik);

        $this->sikService->cancel($sik, $request->user());

        AuditLogService::log(
            'sik',
            'delete',
            "Pengguna {$request->user()->name} membatalkan pengajuan SIK #{$sik->sik_number}",
            ['sik_id' => $sik->id, 'sik_number' => $sik->sik_number],
            $request->user()
        );

        return redirect()->route('sik.index')
            ->with('success', 'Pengajuan SIK berhasil dibatalkan.');
    }

    /**
     * Printable view of SIK.
     */
    public function print(ExitPermit $sik)
    {
        Gate::authorize('view', $sik);

        $sik->load(['user', 'approver', 'exitScannedBy', 'returnScannedBy', 'approvalHistories', 'scanLogs']);

        return view('sik.print', compact('sik'));
    }
}
