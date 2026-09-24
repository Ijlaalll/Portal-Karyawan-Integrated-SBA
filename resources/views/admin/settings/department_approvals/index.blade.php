@extends('layouts.app')

@section('title', 'Konfigurasi Approval Departemen - Admin SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <a href="{{ route('admin.sik.dashboard') }}" class="text-xs text-[#2563EB] hover:underline font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        Dashboard SIK
                    </a>
                    <span class="text-xs text-slate-400">•</span>
                    <span class="text-xs text-slate-500 font-medium">Surat Izin Keluar (SIK)</span>
                </div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Konfigurasi Approval Departemen
                </h1>
                <p class="text-sm text-[#475569] mt-1">Kelola hierarki persetujuan SIK: tetapkan HOD Departemen dan Approver Atasan HOD.</p>
            </div>
            <button type="button" onclick="openAddModal()" class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors self-start sm:self-auto">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Tambah Konfigurasi Departemen
            </button>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 shadow-sm flex items-center gap-3">
                <span class="material-symbols-outlined text-green-600 text-[22px]">check_circle</span>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-600 text-[22px] mt-0.5">error</span>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">Mohon perbaiki kesalahan berikut:</h3>
                        <ul class="mt-1 text-xs text-red-700 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Unconfigured Departments Quick Alert -->
        @php
            $configuredDeptNames = $configs->pluck('department_name')->toArray();
            $unconfiguredDepts = array_filter($discoveredDepts, fn($d) => !in_array($d, $configuredDeptNames));
        @endphp

        @if(count($unconfiguredDepts) > 0)
            <div class="mb-6 rounded-xl bg-amber-50 p-4 border border-amber-200 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-[22px] mt-0.5">info</span>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">Departemen Belum Terkonfigurasi:</h3>
                        <p class="text-xs text-amber-700 mt-0.5">Ditemukan pengguna dengan departemen berikut yang belum memiliki konfigurasi HOD:</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($unconfiguredDepts as $uDept)
                                <button type="button" onclick="quickAddDept('{{ $uDept }}')" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-amber-100 text-amber-800 text-xs font-semibold hover:bg-amber-200 transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">add</span>
                                    {{ $uDept }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Configs Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama Departemen</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">HOD (Default Approver)</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Approver Atasan HOD</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Approver Pengganti (Resmi)</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Catatan</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white text-sm text-[#0F172A]">
                        @forelse ($configs as $config)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                        <span class="material-symbols-outlined text-blue-600 text-[18px]">corporate_fare</span>
                                        {{ $config->department_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($config->hod)
                                        <div class="font-semibold text-slate-900">{{ $config->hod->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $config->hod->email }}</div>
                                    @else
                                        <span class="text-xs text-amber-600 font-medium italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($config->hodApprover)
                                        <div class="font-semibold text-slate-900">{{ $config->hodApprover->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $config->hodApprover->email }}</div>
                                    @else
                                        <span class="text-xs text-amber-600 font-medium italic">Belum ditentukan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-700">
                                    @if($config->replacementApprovers->count() > 0)
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($config->replacementApprovers as $rep)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                                    <span class="material-symbols-outlined text-[13px]">person</span>
                                                    {{ $rep->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Tidak ada approver pengganti</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600 max-w-xs truncate">
                                    {{ $config->notes ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="openEditModal({{ json_encode($config) }})" 
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:bg-slate-200 transition-colors cursor-pointer"
                                            style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.settings.department-approvals.destroy', $config) }}" method="POST" class="inline" onsubmit="return confirm('Hapus konfigurasi approval untuk departemen {{ $config->department_name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                style="background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;">
                                                <span class="material-symbols-outlined text-[15px]">delete</span>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">domain_verification</span>
                                    Belum ada konfigurasi approval departemen yang didaftarkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modal Add/Edit Department Approval Config -->
<div id="configModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-150"
        style="border: 1px solid #E5E7EB;">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3" style="border-bottom: 1px solid #E5E7EB;">
            <h3 id="modalTitle" class="text-base font-bold text-[#0F172A]">Tambah Konfigurasi Approval Departemen</h3>
            <button type="button" onclick="closeConfigModal()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="configForm" method="POST" class="space-y-4">
            @csrf
            <div id="methodContainer"></div>

            <div>
                <label for="department_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Departemen <span class="text-red-500">*</span>
                </label>
                <input type="text" name="department_name" id="modalDeptName" required placeholder="mis. Production, IT & Automation, GA"
                    class="w-full rounded-lg px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-colors"
                    style="border: 1px solid #CBD5E1;">
            </div>

            <div>
                <label for="hod_user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    HOD Departemen (Default Approver Staff)
                </label>
                <select name="hod_user_id" id="modalHod" 
                    class="w-full rounded-lg px-3 py-2 text-xs bg-white text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-colors cursor-pointer"
                    style="border: 1px solid #CBD5E1;">
                    <option value="">-- Pilih Pengguna HOD --</option>
                    @foreach($activeUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->department ?: 'No Dept' }} - {{ $u->position ?: 'Staff' }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">HOD akan menjadi default approver bagi seluruh staff di departemen ini.</p>
            </div>

            <div>
                <label for="hod_approver_user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Approver Atasan HOD (Menyetujui SIK saat HOD mengajukan)
                </label>
                <select name="hod_approver_user_id" id="modalHodApprover" 
                    class="w-full rounded-lg px-3 py-2 text-xs bg-white text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-colors cursor-pointer"
                    style="border: 1px solid #CBD5E1;">
                    <option value="">-- Pilih Atasan HOD / GM / Plant Head --</option>
                    @foreach($activeUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->department ?: 'No Dept' }} - {{ $u->position ?: 'Staff' }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Atasan yang akan menyetujui jika HOD sendiri mengajukan permohonan SIK.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Approver Pengganti yang Berwenang (Eligible Replacement Approvers)
                </label>
                <p class="text-[11px] text-slate-500 mb-2">Pilih user yang berwenang dipilih pemohon sebagai Approver Pengganti untuk departemen ini.</p>
                <div class="max-h-40 overflow-y-auto p-3 rounded-lg border border-slate-200 bg-slate-50 space-y-2">
                    @foreach($activeUsers as $u)
                        <label class="flex items-center gap-2 text-xs text-slate-700 hover:text-slate-900 cursor-pointer">
                            <input type="checkbox" name="replacement_approver_ids[]" value="{{ $u->id }}" class="rounded text-[#2563EB] focus:ring-[#2563EB] replacement-checkbox">
                            <span>{{ $u->name }} <span class="text-slate-400">({{ $u->department ?: 'No Dept' }} - {{ $u->position ?: 'Staff' }})</span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Catatan Konfigurasi (Opsional)
                </label>
                <textarea name="notes" id="modalNotes" rows="2" placeholder="Catatan internal alur delegasi..."
                    class="w-full rounded-lg px-3 py-2 text-xs text-[#0F172A] focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-colors"
                    style="border: 1px solid #CBD5E1;"></textarea>
            </div>

            <div class="flex justify-end gap-2.5 pt-4" style="border-top: 1px solid #E5E7EB;">
                <button type="button" onclick="closeConfigModal()" 
                    class="px-4 py-2 rounded-lg text-xs font-semibold hover:bg-slate-100 transition-colors cursor-pointer"
                    style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                    Batal
                </button>
                <button type="submit" 
                    class="px-5 py-2 rounded-lg text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
                    style="background-color: #2563EB;">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Konfigurasi Approval Departemen';
        document.getElementById('configForm').action = "{{ route('admin.settings.department-approvals.store') }}";
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('modalDeptName').value = '';
        document.getElementById('modalHod').value = '';
        document.getElementById('modalHodApprover').value = '';
        document.getElementById('modalNotes').value = '';
        document.querySelectorAll('.replacement-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('configModal').classList.remove('hidden');
    }

    function quickAddDept(deptName) {
        openAddModal();
        document.getElementById('modalDeptName').value = deptName;
    }

    function openEditModal(config) {
        document.getElementById('modalTitle').textContent = 'Edit Konfigurasi: ' + config.department_name;
        document.getElementById('configForm').action = "/admin/settings/department-approvals/" + config.id;
        document.getElementById('methodContainer').innerHTML = '@method("PUT")';
        document.getElementById('modalDeptName').value = config.department_name;
        document.getElementById('modalHod').value = config.hod_user_id || '';
        document.getElementById('modalHodApprover').value = config.hod_approver_user_id || '';
        document.getElementById('modalNotes').value = config.notes || '';
        
        const repIds = (config.replacement_approvers || []).map(r => String(r.id));
        document.querySelectorAll('.replacement-checkbox').forEach(cb => {
            cb.checked = repIds.includes(String(cb.value));
        });

        document.getElementById('configModal').classList.remove('hidden');
    }

    function closeConfigModal() {
        document.getElementById('configModal').classList.add('hidden');
    }
</script>
@endsection
