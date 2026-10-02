@extends('layouts.app')

@section('title', 'Edit Pengguna - Admin SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Edit Pengguna
        </h1>
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Kembali ke Pengguna
        </a>
    </header>
    
    <main>
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg p-6 sm:p-8 shadow-sm">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-[#0F172A]">Nama</label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}" placeholder="mis. Andi Wijaya"
                            class="mt-1 block w-full rounded-md border @error('name') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600" id="name-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-[#0F172A]">Email</label>
                        <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" placeholder="mis. andi@example.com"
                            class="mt-1 block w-full rounded-md border @error('email') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-[#0F172A]">Kata Sandi (Biarkan kosong jika tidak diubah)</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border @error('password') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-[#0F172A]">Konfirmasi Kata Sandi</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-[#0F172A]">Peran</label>
                        <select name="role" id="role" required
                            class="mt-1 block w-full rounded-md border @error('role') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                            <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Pengguna</option>
                            <option value="spv" {{ old('role', $user->role) === 'spv' ? 'selected' : '' }}>SPV</option>
                            <option value="ga" {{ old('role', $user->role) === 'ga' ? 'selected' : '' }}>GA (General Affairs)</option>
                            <option value="it" {{ old('role', $user->role) === 'it' ? 'selected' : '' }}>IT Support</option>
                            <option value="security" {{ old('role', $user->role) === 'security' ? 'selected' : '' }}>Security (Petugas Pos Gerbang)</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600" id="role-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div>
                        <label for="department" class="block text-sm font-medium text-[#0F172A]">Departemen</label>
                        <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}" placeholder="mis. Information Technology, Mining, GA"
                            class="mt-1 block w-full rounded-md border @error('department') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('department')
                            <p class="mt-1 text-sm text-red-600" id="department-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Position / Jabatan -->
                    <div>
                        <label for="position" class="block text-sm font-medium text-[#0F172A]">Jabatan / Posisi</label>
                        <input type="text" name="position" id="position" value="{{ old('position', $user->position) }}" placeholder="mis. Staff Mekanikal, Supervisor GA, HOD Mining"
                            class="mt-1 block w-full rounded-md border @error('position') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('position')
                            <p class="mt-1 text-sm text-red-600" id="position-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Employee ID -->
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-[#0F172A]">Nomor Karyawan (NIP / ID)</label>
                        <input type="text" name="employee_id" id="employee_id" value="{{ old('employee_id', $user->employee_id) }}" placeholder="mis. SBA-2024-001"
                            class="mt-1 block w-full rounded-md border @error('employee_id') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-600" id="employee_id-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Joined At -->
                    <div>
                        <label for="joined_at" class="block text-sm font-medium text-[#0F172A]">Tanggal Bergabung (Joined At)</label>
                        <input type="date" name="joined_at" id="joined_at" value="{{ old('joined_at', $user->joined_at ? $user->joined_at->format('Y-m-d') : '') }}"
                            class="mt-1 block w-full rounded-md border @error('joined_at') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                        @error('joined_at')
                            <p class="mt-1 text-sm text-red-600" id="joined_at-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cost Center -->
                    <div>
                        <label for="cost_center_id" class="block text-sm font-medium text-[#0F172A]">Cost Center</label>
                        <select name="cost_center_id" id="cost_center_id"
                            class="mt-1 block w-full rounded-md border @error('cost_center_id') border-red-300 focus:border-red-500 focus:ring-red-500 @else border-[#E5E7EB] focus:border-[#2563EB] focus:ring-[#2563EB] @enderror px-3 py-2 text-[#0F172A] shadow-sm focus:outline-none focus:ring-1 sm:text-sm">
                            <option value="">-- Pilih Cost Center (Opsional) --</option>
                            @foreach($costCenters as $cc)
                                <option value="{{ $cc->id }}" {{ old('cost_center_id', $user->cost_center_id) == $cc->id ? 'selected' : '' }}>
                                    {{ $cc->cost_center_code }} - {{ $cc->name ?: $cc->description }} {{ $cc->status === 'inactive' ? '(Nonaktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-[#64748B]">Hanya menampilkan unit Cost Center yang berstatus Aktif (atau yang saat ini terpasang).</p>
                        @error('cost_center_id')
                            <p class="mt-1 text-sm text-red-600" id="cost_center_id-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="management-access-container">
                        <span class="block text-sm font-medium text-[#0F172A] mb-3">Akses Manajemen</span>
                        <div class="space-y-3">
                            @php
                                $assignedModules = old('modules', $user->modules->pluck('module')->toArray());
                            @endphp
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_meeting_room" name="modules[]" type="checkbox" value="meeting_room"
                                        {{ is_array($assignedModules) && in_array('meeting_room', $assignedModules) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_meeting_room" class="font-medium text-[#0F172A]">Ruang Meeting</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_toilet_inspection" name="modules[]" type="checkbox" value="toilet_inspection"
                                        {{ is_array($assignedModules) && in_array('toilet_inspection', $assignedModules) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_toilet_inspection" class="font-medium text-[#0F172A]">Inspeksi Toilet</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_atk" name="modules[]" type="checkbox" value="atk"
                                        {{ is_array($assignedModules) && in_array('atk', $assignedModules) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_atk" class="font-medium text-[#0F172A]">ATK (Alat Tulis Kantor)</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_sik" name="modules[]" type="checkbox" value="sik"
                                        {{ is_array($assignedModules) && in_array('sik', $assignedModules) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_sik" class="font-medium text-[#0F172A]">Surat Izin Keluar (SIK)</label>
                                </div>
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-5 items-center">
                                    <input id="module_vehicle" name="modules[]" type="checkbox" value="vehicle"
                                        {{ is_array($assignedModules) && in_array('vehicle', $assignedModules) ? 'checked' : '' }}
                                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="module_vehicle" class="font-medium text-[#0F172A]">Kendaraan (Manajemen Kendaraan)</label>
                                </div>
                            </div>
                        </div>
                        @error('modules')
                            <p class="mt-1 text-sm text-red-600" id="modules-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="admin-management-info" class="hidden">
                        <span class="block text-sm font-medium text-[#0F172A] mb-1">Akses Manajemen</span>
                        <p class="text-sm text-[#475569] bg-[#F8FAFC] border border-[#E2E8F0] rounded-md px-3 py-2">
                            Semua Manajemen (diberikan otomatis untuk Admin)
                        </p>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                    <a href="{{ route('admin.users.index') }}" class="rounded bg-white px-3 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</a>
                    <button type="submit" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                        Perbarui Pengguna
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    function initUserEditForm() {
        const roleSelect = document.getElementById('role');
        const managementContainer = document.getElementById('management-access-container');
        const adminInfo = document.getElementById('admin-management-info');

        if (!roleSelect || !managementContainer) return;

        function toggleManagementAccess() {
            if (roleSelect.value === 'admin') {
                managementContainer.style.display = 'none';
                if (adminInfo) adminInfo.classList.remove('hidden');
            } else {
                managementContainer.style.display = 'block';
                if (adminInfo) adminInfo.classList.add('hidden');
            }
        }

        roleSelect.removeEventListener('change', toggleManagementAccess);
        roleSelect.addEventListener('change', toggleManagementAccess);
        toggleManagementAccess(); // Run on load
    }

    if (window.onPageLoad) {
        window.onPageLoad(initUserEditForm);
    } else {
        document.addEventListener('DOMContentLoaded', initUserEditForm);
    }
</script>
@endsection
