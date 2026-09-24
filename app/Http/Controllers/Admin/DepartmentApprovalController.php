<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DepartmentApproval;
use App\Models\User;
use App\Services\DepartmentApprovalService;
use Illuminate\Http\Request;

class DepartmentApprovalController extends Controller
{
    public function __construct(
        protected DepartmentApprovalService $departmentApprovalService
    ) {}

    /**
     * Display department approvals configuration list.
     */
    public function index(Request $request)
    {
        $configs = DepartmentApproval::with(['hod', 'hodApprover', 'replacementApprovers'])
            ->orderBy('department_name', 'asc')
            ->get();

        $activeUsers = User::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        // Discover existing department names from users table
        $discoveredDepts = User::whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->pluck('department')
            ->toArray();

        return view('admin.settings.department_approvals.index', compact('configs', 'activeUsers', 'discoveredDepts'));
    }

    /**
     * Store a newly created department approval config.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:255', 'unique:department_approvals,department_name'],
            'hod_user_id' => ['nullable', 'exists:users,id'],
            'hod_approver_user_id' => ['nullable', 'exists:users,id', 'different:hod_user_id'],
            'replacement_approver_ids' => ['nullable', 'array'],
            'replacement_approver_ids.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'department_name.required' => 'Nama Departemen wajib diisi.',
            'department_name.unique' => 'Konfigurasi untuk departemen ini sudah ada.',
            'hod_approver_user_id.different' => 'HOD dan Approver Atasan HOD tidak boleh merupakan pengguna yang sama.',
        ]);

        $replacementIds = array_map('intval', $request->input('replacement_approver_ids', []));

        $this->departmentApprovalService->saveDepartmentConfig(
            $validated['department_name'],
            $validated['hod_user_id'] ? (int) $validated['hod_user_id'] : null,
            $validated['hod_approver_user_id'] ? (int) $validated['hod_approver_user_id'] : null,
            $validated['notes'] ?? null,
            $replacementIds
        );

        return redirect()->route('admin.settings.department-approvals.index')
            ->with('success', "Konfigurasi approval departemen '{$validated['department_name']}' berhasil disimpan.");
    }

    /**
     * Update the specified department approval config.
     */
    public function update(Request $request, DepartmentApproval $departmentApproval)
    {
        $validated = $request->validate([
            'department_name' => ['required', 'string', 'max:255', 'unique:department_approvals,department_name,' . $departmentApproval->id],
            'hod_user_id' => ['nullable', 'exists:users,id'],
            'hod_approver_user_id' => ['nullable', 'exists:users,id', 'different:hod_user_id'],
            'replacement_approver_ids' => ['nullable', 'array'],
            'replacement_approver_ids.*' => ['exists:users,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'department_name.required' => 'Nama Departemen wajib diisi.',
            'hod_approver_user_id.different' => 'HOD dan Approver Atasan HOD tidak boleh merupakan pengguna yang sama.',
        ]);

        $replacementIds = array_map('intval', $request->input('replacement_approver_ids', []));

        $this->departmentApprovalService->saveDepartmentConfig(
            $validated['department_name'],
            $validated['hod_user_id'] ? (int) $validated['hod_user_id'] : null,
            $validated['hod_approver_user_id'] ? (int) $validated['hod_approver_user_id'] : null,
            $validated['notes'] ?? null,
            $replacementIds
        );

        return redirect()->route('admin.settings.department-approvals.index')
            ->with('success', "Konfigurasi approval departemen '{$validated['department_name']}' berhasil diperbarui.");
    }

    /**
     * Remove the specified department approval config.
     */
    public function destroy(DepartmentApproval $departmentApproval)
    {
        $deptName = $departmentApproval->department_name;
        $departmentApproval->delete();

        return redirect()->route('admin.settings.department-approvals.index')
            ->with('success', "Konfigurasi approval departemen '{$deptName}' berhasil dihapus.");
    }
}
