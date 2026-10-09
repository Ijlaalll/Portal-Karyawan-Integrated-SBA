@extends('layouts.app')

@section('title', 'Manajemen Pengguna - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Manajemen Pengguna
            </h1>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="openImportModal()" class="rounded bg-white border border-[#CBD5E1] px-3.5 py-2 text-sm font-semibold text-[#334155] shadow-sm hover:bg-slate-50 hover:border-slate-400 transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#64748B]">upload_file</span>
                    Import Karyawan
                </button>
                <a href="{{ route('admin.users.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                    + Tambah Pengguna
                </a>
            </div>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Search and Filters -->
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
            <!-- Search bar -->
            <div class="relative flex-1 max-w-md">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengguna..."
                    class="block w-full rounded-md border border-[#E5E7EB] pl-10 pr-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] sm:text-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                </div>
            </div>

            <!-- Dropdowns & Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <select name="management" onchange="submitFormWithTurbo(this)"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('management') === 'all' ? 'selected' : '' }}>Semua Manajemen</option>
                    <option value="meeting_room" {{ request('management') === 'meeting_room' ? 'selected' : '' }}>Ruang Meeting</option>
                    <option value="toilet_inspection" {{ request('management') === 'toilet_inspection' ? 'selected' : '' }}>Inspeksi Toilet</option>
                    <option value="atk" {{ request('management') === 'atk' ? 'selected' : '' }}>ATK (Alat Tulis Kantor)</option>
                    <option value="sik" {{ request('management') === 'sik' ? 'selected' : '' }}>Surat Izin Keluar (SIK)</option>
                    <option value="vehicle" {{ request('management') === 'vehicle' ? 'selected' : '' }}>Kendaraan</option>
                </select>

                <select name="role" onchange="submitFormWithTurbo(this)"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Semua Peran</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Pengguna</option>
                    <option value="spv" {{ request('role') === 'spv' ? 'selected' : '' }}>SPV</option>
                    <option value="ga" {{ request('role') === 'ga' ? 'selected' : '' }}>GA</option>
                    <option value="it" {{ request('role') === 'it' ? 'selected' : '' }}>IT</option>
                    <option value="security" {{ request('role') === 'security' ? 'selected' : '' }}>Security</option>
                </select>

                <select name="status" onchange="submitFormWithTurbo(this)"
                    class="rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>

                @if(request()->filled('search') || (request()->filled('management') && request('management') !== 'all') || (request()->filled('role') && request('role') !== 'all') || (request()->filled('status') && request('status') !== 'all'))
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-[#475569] hover:text-[#0F172A] font-medium underline transition-all">
                        Reset Filter
                    </a>
                @endif
                
                <button type="submit" class="hidden">Cari</button>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-8">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">group</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Total Pengguna">Total Pengguna</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $totalUsers }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">meeting_room</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Ruang Meeting">Ruang Meeting</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $meetingRoomCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">wc</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Inspeksi Toilet">Inspeksi Toilet</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $toiletInspectionCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-green-50 text-green-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">inventory_2</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="ATK">ATK</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $atkCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">badge</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="SIK">SIK</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $sikCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-3.5 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">directions_car</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Kendaraan">Kendaraan</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 leading-tight">{{ $vehicleCount }}</p>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Peran</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Departemen</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Cost Center</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Manajemen</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Bergabung Pada</th>
                        <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4.5 whitespace-nowrap font-semibold text-[#0F172A]">{{ $user->name }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569]">{{ $user->email }}</td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @php
                                    $roleStyles = [
                                        'admin' => 'background-color: #EFF6FF; color: #2563EB; box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.2);',
                                        'spv' => 'background-color: #FAF5FF; color: #9333EA; box-shadow: inset 0 0 0 1px rgba(147, 51, 234, 0.2);',
                                        'user' => 'background-color: #F8FAFC; color: #64748B; box-shadow: inset 0 0 0 1px rgba(226, 232, 240, 1);',
                                        'ga' => 'background-color: #FEF3C7; color: #D97706; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.2);',
                                        'it' => 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);',
                                        'security' => 'background-color: #FFF7ED; color: #C2410C; box-shadow: inset 0 0 0 1px rgba(194, 65, 12, 0.2);',
                                    ];
                                    $roleIcons = [
                                        'admin' => 'admin_panel_settings',
                                        'security' => 'local_police',
                                        'spv' => 'supervisor_account',
                                        'ga' => 'storefront',
                                        'it' => 'terminal',
                                        'user' => 'person',
                                    ];
                                    $roleLabels = [
                                        'admin' => 'Admin',
                                        'security' => 'Security',
                                        'spv' => 'SPV',
                                        'ga' => 'GA',
                                        'it' => 'IT',
                                        'user' => 'Pengguna',
                                    ];
                                    $style = $roleStyles[$user->role] ?? $roleStyles['user'];
                                    $icon = $roleIcons[$user->role] ?? 'person';
                                    $label = $roleLabels[$user->role] ?? ucfirst($user->role);
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-semibold" style="{{ $style }}">
                                    <span class="material-symbols-outlined text-[15px]">{{ $icon }}</span>
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-xs text-[#475569] font-medium">
                                {{ $user->department ?: '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-xs font-bold text-[#2563EB]">
                                {{ $user->costCenter ? $user->costCenter->cost_center_code : '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1.5">
                                    @if ($user->isAdmin())
                                        <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2.5 py-1 text-xs font-medium text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                                            Semua Manajemen
                                        </span>
                                    @elseif ($user->isSecurity())
                                        <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium" style="background-color: #FFF7ED; color: #C2410C; box-shadow: inset 0 0 0 1px rgba(194, 65, 12, 0.2);">
                                            <span class="material-symbols-outlined text-[15px]" style="color: #C2410C;">qr_code_scanner</span>
                                            Pos Security (SIK Scanner)
                                        </span>
                                    @else
                                        @forelse ($user->modules as $mod)
                                            @php
                                                $modNames = [
                                                    'meeting_room' => 'Ruang Meeting',
                                                    'toilet_inspection' => 'Inspeksi Toilet',
                                                    'atk' => 'ATK',
                                                    'sik' => 'SIK',
                                                    'vehicle' => 'Kendaraan',
                                                ];
                                                $modName = $modNames[$mod->module] ?? ucfirst(str_replace('_', ' ', $mod->module));
                                                
                                                $modStyles = [
                                                    'meeting_room' => 'background-color: #EFF6FF; color: #2563EB; border: 1px solid #BFDBFE;',
                                                    'toilet_inspection' => 'background-color: #FFFBEB; color: #D97706; border: 1px solid #FDE68A;',
                                                    'atk' => 'background-color: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0;',
                                                    'sik' => 'background-color: #EEF2FF; color: #4F46E5; border: 1px solid #C7D2FE;',
                                                    'vehicle' => 'background-color: #F0F9FF; color: #0284C7; border: 1px solid #BAE6FD;',
                                                ];
                                                $modStyle = $modStyles[$mod->module] ?? 'background-color: #F8FAFC; color: #64748B; border: 1px solid #E2E8F0;';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium" style="{{ $modStyle }}">
                                                {{ $modName }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-[#94A3B8] italic">Tidak ada akses</span>
                                        @endforelse
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @if ($user->status === 'active')
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-[#475569]">
                                {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="py-4.5 whitespace-nowrap text-right text-xs font-semibold" style="padding-left: 1.5rem; padding-right: 1.5rem;">
                                <div class="flex items-center justify-end gap-2">
                                     <a href="{{ route('admin.users.show', $user) }}" 
                                         class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity"
                                         style="width: 75px; min-width: 75px; background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                                         <span class="material-symbols-outlined text-[15px]">visibility</span>
                                         Lihat
                                     </a>
                                     <a href="{{ route('admin.users.edit', $user) }}" 
                                         class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:bg-slate-200 transition-colors"
                                         style="width: 70px; min-width: 70px; background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                         <span class="material-symbols-outlined text-[15px]">edit</span>
                                         Edit
                                     </a>
                                     
                                     @php
                                         $hasHistory = $user->hasHistory();
                                     @endphp
                                     
                                     @if ($user->role === 'admin')
                                         @if ($user->status === 'active')
                                             <button type="button" 
                                                 onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.deactivate', $user) }}', 'deactivate')" 
                                                 class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                 style="width: 115px; min-width: 115px; background-color: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3;">
                                                 <span class="material-symbols-outlined text-[15px]">block</span>
                                                 Nonaktifkan
                                             </button>
                                         @else
                                             <button type="button" 
                                                 onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.activate', $user) }}', 'activate')" 
                                                 class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                 style="width: 115px; min-width: 115px; background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                                 <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                                 Aktifkan
                                             </button>
                                         @endif
                                     @else
                                         @if (!$hasHistory)
                                             <button type="button" 
                                                 onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.destroy', $user) }}', 'delete')" 
                                                 class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                 style="width: 115px; min-width: 115px; background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;">
                                                 <span class="material-symbols-outlined text-[15px]">delete</span>
                                                 Hapus
                                             </button>
                                         @else
                                             @if ($user->status === 'active')
                                                 <button type="button" 
                                                     onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.deactivate', $user) }}', 'deactivate')" 
                                                     class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                     style="width: 115px; min-width: 115px; background-color: #FFF1F2; color: #BE123C; border: 1px solid #FECDD3;">
                                                     <span class="material-symbols-outlined text-[15px]">block</span>
                                                     Nonaktifkan
                                                 </button>
                                             @else
                                                 <button type="button" 
                                                     onclick="openStatusModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.activate', $user) }}', 'activate')" 
                                                     class="inline-flex items-center justify-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold shrink-0 hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                                     style="width: 115px; min-width: 115px; background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                                     <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                                     Aktifkan
                                                 </button>
                                             @endif
                                         @endif
                                     @endif
                                </div>
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-[#475569] bg-white">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="material-symbols-outlined text-4xl text-[#94A3B8]">group_off</span>
                                    <p class="font-medium text-[#475569]">Pengguna tidak ditemukan.</p>
                                    <p class="text-xs text-[#94A3B8]">Coba tambahkan pengguna baru atau sesuaikan filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </main>
</div>

<!-- Dynamic Status Confirmation Modal -->
<div id="status-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-[#0F172A]/40 transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
            <div class="sm:flex sm:items-start">
                <div id="modal-icon-container" class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <span id="modal-icon" class="material-symbols-outlined text-[24px]">warning</span>
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]" id="modal-title">Judul Tindakan</h3>
                    <div class="mt-2">
                        <p class="text-sm text-[#475569]" id="modal-text">
                            Deskripsi tindakan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                <form id="modal-form" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="_method" id="modal-method" value="POST">
                    <button type="submit" id="modal-submit-btn" class="inline-flex w-full justify-center rounded px-4 py-2 text-sm font-semibold text-white shadow-sm sm:w-auto transition-colors">
                        Kirim
                    </button>
                </form>
                <button type="button" onclick="closeStatusModal()" class="mt-3 inline-flex w-full justify-center rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB] sm:mt-0 sm:w-auto transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Import CSV Karyawan -->
<div id="import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="import-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6 border border-slate-100">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">upload_file</span>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#0F172A]" id="import-modal-title">
                            Import Data Karyawan (CSV)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Unggah file CSV untuk mendaftarkan karyawan secara massal.</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Download Template Box -->
            <div class="mt-4 p-3 bg-blue-50/70 border border-blue-100 rounded-xl flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[#2563EB] text-[20px]">description</span>
                    <div>
                        <p class="text-xs font-bold text-[#0F172A]">Belum punya formatnya?</p>
                        <p class="text-[11px] text-slate-500">Unduh template CSV yang sudah sesuai kolom sistem.</p>
                    </div>
                </div>
                <a href="{{ route('admin.users.template') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-blue-200 text-[#2563EB] hover:bg-blue-50 rounded-lg text-xs font-bold shadow-sm transition-all shrink-0">
                    <span class="material-symbols-outlined text-[15px]">download</span>
                    Unduh Template
                </a>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pilih File CSV <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" accept=".csv,.txt" required
                        class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 border border-slate-200 rounded-xl cursor-pointer bg-slate-50/50 p-1.5 focus:outline-none focus:border-[#2563EB]">
                    <p class="text-[10px] text-slate-400 mt-1">Mendukung format .csv (pemisah koma atau titik koma), maksimal 5MB.</p>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="update_existing" id="update_existing" value="1" checked
                        class="rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] h-4 w-4">
                    <label for="update_existing" class="text-xs text-slate-600 select-none">
                        Perbarui data jika email atau NIP sudah terdaftar
                    </label>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">cloud_upload</span>
                        Unggah & Impor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function openImportModal() {
        document.getElementById('import-modal').classList.remove('hidden');
    }

    function closeImportModal() {
        document.getElementById('import-modal').classList.add('hidden');
    }

    function openStatusModal(userId, userName, actionUrl, actionType) {
        const modal = document.getElementById('status-modal');
        const title = document.getElementById('modal-title');
        const text = document.getElementById('modal-text');
        const form = document.getElementById('modal-form');
        const methodInput = document.getElementById('modal-method');
        const submitBtn = document.getElementById('modal-submit-btn');
        const iconContainer = document.getElementById('modal-icon-container');
        const icon = document.getElementById('modal-icon');
        const safeUserName = escapeHtml(userName);

        form.action = actionUrl;

        if (actionType === 'delete') {
            title.textContent = 'Hapus Pengguna';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-red-600 text-[24px]';
            icon.textContent = 'warning';
            text.innerHTML = 'Apakah Anda yakin ingin menghapus <span class="font-bold text-[#0F172A]">' + safeUserName + '</span> secara permanen? Tindakan ini hanya tersedia karena pengguna ini tidak memiliki riwayat aktivitas. Tindakan ini tidak dapat dibatalkan.';
            methodInput.value = 'DELETE';
            submitBtn.textContent = 'Hapus Pengguna';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:w-auto transition-colors';
        } else if (actionType === 'deactivate') {
            title.textContent = 'Nonaktifkan Pengguna';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-amber-600 text-[24px]';
            icon.textContent = 'block';
            text.innerHTML = 'Apakah Anda yakin ingin menonaktifkan <span class="font-bold text-[#0F172A]">' + safeUserName + '</span>? Hal ini akan mencegah pengguna untuk masuk ke sistem, tetapi data riwayat mereka akan tetap disimpan.';
            methodInput.value = 'POST';
            submitBtn.textContent = 'Nonaktifkan';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#D97706] sm:w-auto transition-colors';
        } else if (actionType === 'activate') {
            title.textContent = 'Aktifkan Pengguna';
            iconContainer.className = 'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-50 sm:mx-0 sm:h-10 sm:w-10';
            icon.className = 'material-symbols-outlined text-green-600 text-[24px]';
            icon.textContent = 'check_circle';
            text.innerHTML = 'Apakah Anda yakin ingin mengaktifkan <span class="font-bold text-[#0F172A]">' + safeUserName + '</span>? Hal ini akan memulihkan akses pengguna ke aplikasi.';
            methodInput.value = 'POST';
            submitBtn.textContent = 'Aktifkan';
            submitBtn.className = 'inline-flex w-full justify-center rounded bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:w-auto transition-colors';
        }

        modal.classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('status-modal').classList.add('hidden');
    }
</script>
@endsection
