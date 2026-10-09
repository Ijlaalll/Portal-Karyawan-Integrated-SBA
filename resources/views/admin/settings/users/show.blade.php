@extends('layouts.app')

@section('title', 'Detail Pengguna - Admin SBA')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <!-- Header -->
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Detail Profil Pengguna
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Kelola dan lihat log aktivitas untuk {{ $user->name }}
            </p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
            Kembali ke Pengguna
        </a>
    </header>

    <!-- Profile Overview & Summary Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Informasi Profil</h2>
            
            <div class="space-y-3 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Nama Lengkap</span>
                    <span class="text-[#0F172A] font-medium text-base">{{ $user->name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Alamat Email</span>
                    <span class="text-[#475569]">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Nomor Karyawan (NIP)</span>
                    <span class="text-[#0F172A] font-mono font-semibold">{{ $user->employee_id ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Departemen</span>
                    <span class="text-[#0F172A] font-medium">{{ $user->department ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Jabatan / Posisi</span>
                    <span class="text-[#0F172A] font-medium">{{ $user->position ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Cost Center</span>
                    <span class="text-[#0F172A] font-medium">{{ $user->costCenter ? $user->costCenter->cost_center_code . ' - ' . ($user->costCenter->name ?: $user->costCenter->description) : '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Peran</span>
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
                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 mt-1 text-xs font-semibold" style="{{ $style }}">
                        <span class="material-symbols-outlined text-[15px]">{{ $icon }}</span>
                        {{ $label }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Status</span>
                    @if ($user->status === 'active')
                        <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 mt-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                    @else
                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 mt-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                    @endif
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Bergabung Pada</span>
                    <span class="text-[#475569]">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Akses Manajemen</span>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        @if ($user->isAdmin())
                            <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2.5 py-0.5 text-xs font-medium text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/20">
                                Semua Manajemen
                            </span>
                        @elseif ($user->isSecurity())
                            <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-0.5 text-xs font-medium" style="background-color: #FFF7ED; color: #C2410C; box-shadow: inset 0 0 0 1px rgba(194, 65, 12, 0.2);">
                                <span class="material-symbols-outlined text-[15px]" style="color: #C2410C;">qr_code_scanner</span>
                                Pos Security (SIK Scanner)
                            </span>
                        @else
                            @forelse ($user->modules as $mod)
                                @php
                                    $modNames = [
                                        'meeting_room' => 'Ruang Meeting',
                                        'toilet_inspection' => 'Inspeksi Toilet',
                                        'atk' => 'ATK (Alat Tulis Kantor)',
                                        'sik' => 'Surat Izin Keluar (SIK)',
                                        'vehicle' => 'Kendaraan (Manajemen Kendaraan)',
                                    ];
                                    $modClasses = [
                                        'meeting_room' => 'bg-[#EFF6FF] text-[#2563EB] ring-[#2563EB]/20',
                                        'toilet_inspection' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                        'atk' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'sik' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                        'vehicle' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                    ];
                                    $modName = $modNames[$mod->module] ?? ucfirst(str_replace('_', ' ', $mod->module));
                                    $modClass = $modClasses[$mod->module] ?? 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium {{ $modClass }} ring-1 ring-inset">
                                    {{ $modName }}
                                </span>
                            @empty
                                <span class="text-xs text-[#94A3B8] italic">Akses belum dikonfigurasi</span>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Stats Cards (adapted to access) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Meeting Room Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('meeting_room') || $user->isGa() || $user->isIt())
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-[#2563EB]">meeting_room</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            @if ($user->isAdmin())
                                Ringkasan Persetujuan Ruang Meeting
                            @elseif ($user->isGa() || $user->isIt())
                                Ringkasan Hasil Kerja Persiapan ({{ strtoupper($user->role) }})
                            @else
                                Ringkasan Aktivitas Ruang Meeting
                            @endif
                        </h3>
                    </div>
                    @if ($user->isGa() || $user->isIt())
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
                            <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-[#0F172A]">{{ $meetingRoomStats['total'] }}</p>
                                <p class="text-[10px] font-bold text-[#64748B] uppercase mt-0.5 truncate" title="Total Tugas">Total Tugas</p>
                            </div>
                            <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-amber-700">{{ $meetingRoomStats['pending'] }}</p>
                                <p class="text-[10px] font-bold text-amber-600 uppercase mt-0.5 truncate" title="Menunggu">Menunggu</p>
                            </div>
                            <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-indigo-700">{{ $meetingRoomStats['in_progress'] }}</p>
                                <p class="text-[10px] font-bold text-indigo-600 uppercase mt-0.5 truncate" title="Dikerjakan">Dikerjakan</p>
                            </div>
                            <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-green-700">{{ $meetingRoomStats['ready'] }}</p>
                                <p class="text-[10px] font-bold text-green-600 uppercase mt-0.5 truncate" title="Siap">Siap</p>
                            </div>
                            <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-red-700">{{ $meetingRoomStats['not_ready'] }}</p>
                                <p class="text-[10px] font-bold text-red-600 uppercase mt-0.5 truncate" title="Tidak Siap">Tidak Siap</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-blue-700">{{ $meetingRoomStats['completed'] }}</p>
                                <p class="text-[10px] font-bold text-blue-600 uppercase mt-0.5 truncate" title="Selesai">Selesai</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-[#0F172A]">{{ $meetingRoomStats['total'] }}</p>
                                <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="Total Pemesanan">Total Pemesanan</p>
                            </div>
                            <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-green-700">{{ $meetingRoomStats['approved'] }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Disetujui">Disetujui</p>
                            </div>
                            @if($user->isAdmin())
                                <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center min-w-0">
                                    <p class="text-2xl font-bold text-red-700">{{ $meetingRoomStats['rejected'] }}</p>
                                    <p class="text-xs font-semibold text-red-600 uppercase mt-0.5 truncate" title="Ditolak">Ditolak</p>
                                </div>
                                <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center min-w-0">
                                    <p class="text-2xl font-bold text-amber-700">{{ $meetingRoomStats['pending'] }}</p>
                                    <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5 truncate" title="Menunggu">Menunggu</p>
                                </div>
                            @else
                                <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center min-w-0">
                                    <p class="text-2xl font-bold text-red-700">{{ $meetingRoomStats['cancelled'] }}</p>
                                    <p class="text-xs font-semibold text-red-600 uppercase mt-0.5 truncate" title="Dibatalkan">Dibatalkan</p>
                                </div>
                                <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg text-center min-w-0">
                                    <p class="text-2xl font-bold text-blue-700">{{ $meetingRoomStats['completed'] }}</p>
                                    <p class="text-xs font-semibold text-blue-600 uppercase mt-0.5 truncate" title="Selesai">Selesai</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <!-- Toilet Inspection Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('toilet_inspection'))
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-[#D97706]">wc</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            @if ($user->isAdmin())
                                Pemantauan Inspeksi Toilet
                            @else
                                Ringkasan Aktivitas Inspeksi Toilet ({{ $user->isSpv() ? 'SPV' : 'Petugas Inspeksi' }})
                            @endif
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                            <p class="text-2xl font-bold text-[#0F172A]">{{ $toiletStats['total'] }}</p>
                            <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="{{ $user->isAdmin() ? 'Total Inspeksi' : ($user->isSpv() ? 'Total Ulasan' : 'Total Inspeksi') }}">
                                @if ($user->isAdmin())
                                    Total Inspeksi
                                @elseif ($user->isSpv())
                                    Total Ulasan
                                @else
                                    Total Inspeksi
                                @endif
                            </p>
                        </div>
                        <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center min-w-0">
                            <p class="text-2xl font-bold text-green-700">{{ $toiletStats['approved'] }}</p>
                            <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="{{ $user->isAdmin() ? 'Disetujui oleh SPV' : 'Disetujui' }}">
                                @if ($user->isAdmin())
                                    Disetujui oleh SPV
                                @else
                                    Disetujui
                                @endif
                            </p>
                        </div>
                        <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center min-w-0">
                            <p class="text-2xl font-bold text-red-700">{{ $toiletStats['rejected'] }}</p>
                            <p class="text-xs font-semibold text-red-600 uppercase mt-0.5 truncate" title="{{ $user->isAdmin() ? 'Ditolak oleh SPV' : 'Ditolak' }}">
                                @if ($user->isAdmin())
                                    Ditolak oleh SPV
                                @else
                                    Ditolak
                                @endif
                            </p>
                        </div>
                        <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center min-w-0">
                            <p class="text-2xl font-bold text-amber-700">{{ $toiletStats['pending'] }}</p>
                            <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5 truncate" title="{{ $user->isAdmin() ? 'Menunggu Ulasan' : ($user->isSpv() ? 'Menunggu Ulasan' : 'Menunggu') }}">
                                @if ($user->isAdmin())
                                    Menunggu Ulasan
                                @elseif ($user->isSpv())
                                    Menunggu Ulasan
                                @else
                                    Menunggu
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- ATK Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('atk'))
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-green-600">inventory_2</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            @if ($user->isAdmin())
                                Pemantauan Permintaan ATK
                            @elseif ($user->isGa())
                                Ringkasan Proses Permintaan ATK (GA)
                            @else
                                Ringkasan Permintaan ATK
                            @endif
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                            <p class="text-2xl font-bold text-[#0F172A]">{{ $atkStats['total'] }}</p>
                            <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="Total Request">Total Request</p>
                        </div>
                        @if ($user->isGa())
                            <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-indigo-700">{{ $atkStats['disetujui'] }}</p>
                                <p class="text-xs font-semibold text-indigo-600 uppercase mt-0.5 truncate" title="Disetujui">Disetujui</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-blue-700">{{ $atkStats['diproses'] }}</p>
                                <p class="text-xs font-semibold text-blue-600 uppercase mt-0.5 truncate" title="Diproses">Diproses</p>
                            </div>
                            <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-green-700">{{ $atkStats['selesai'] }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Selesai">Selesai</p>
                            </div>
                        @else
                            <div class="bg-green-50 border border-green-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-green-700">{{ $atkStats['disetujui'] + $atkStats['selesai'] + ($atkStats['diproses'] ?? 0) }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Disetujui/Selesai">Disetujui/Selesai</p>
                            </div>
                            <div class="bg-red-50 border border-red-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-red-700">{{ $atkStats['ditolak'] + $atkStats['dibatalkan'] }}</p>
                                <p class="text-xs font-semibold text-red-600 uppercase mt-0.5 truncate" title="Ditolak/Batal">Ditolak/Batal</p>
                            </div>
                            <div class="bg-amber-50 border border-amber-100 p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-amber-700">{{ $atkStats['diajukan'] }}</p>
                                <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5 truncate" title="Menunggu">Menunggu</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- SIK Stats -->
            @if ($user->isAdmin() || $user->hasModuleAccess('sik') || $user->isSecurity())
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-[#E5E7EB] pb-2 mb-3">
                        <span class="material-symbols-outlined text-[#7C3AED]">badge</span>
                        <h3 class="text-base font-bold text-[#0F172A]">
                            @if ($user->isAdmin())
                                Pemantauan Surat Izin Keluar (SIK)
                            @elseif ($user->isSecurity())
                                Ringkasan Validasi Gerbang Security
                            @elseif ($user->isSikApprover())
                                Ringkasan Evaluasi Persetujuan SIK (HOD / Atasan)
                            @else
                                Ringkasan Surat Izin Keluar (SIK)
                            @endif
                        </h3>
                    </div>
                    @if ($user->isSecurity())
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-[#0F172A]">{{ $sikStats['total'] }}</p>
                                <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="Total Scan">Total Scan</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #FAF5FF; border: 1px solid #E9D5FF;">
                                <p class="text-2xl font-bold text-purple-700">{{ $sikStats['exit'] }}</p>
                                <p class="text-xs font-semibold text-purple-600 uppercase mt-0.5 truncate" title="Scan Keluar">Scan Keluar</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #F0FDF4; border: 1px solid #DCFCE7;">
                                <p class="text-2xl font-bold text-green-700">{{ $sikStats['return'] }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Scan Kembali">Scan Kembali</p>
                            </div>
                        </div>
                    @elseif ($user->isSikApprover())
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-[#0F172A]">{{ $sikStats['total'] }}</p>
                                <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="Total Diajukan">Total Diajukan</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #F0FDF4; border: 1px solid #DCFCE7;">
                                <p class="text-2xl font-bold text-green-700">{{ $sikStats['approved'] }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Disetujui">Disetujui</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #FFFBEB; border: 1px solid #FEF3C7;">
                                <p class="text-2xl font-bold text-amber-700">{{ $sikStats['pending'] }}</p>
                                <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5 truncate" title="Menunggu">Menunggu</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #FEF2F2; border: 1px solid #FEE2E2;">
                                <p class="text-2xl font-bold text-red-700">{{ $sikStats['rejected'] }}</p>
                                <p class="text-xs font-semibold text-red-600 uppercase mt-0.5 truncate" title="Ditolak">Ditolak</p>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="bg-[#F8FAFC] border border-[#E5E7EB] p-3 rounded-lg text-center min-w-0">
                                <p class="text-2xl font-bold text-[#0F172A]">{{ $sikStats['total'] }}</p>
                                <p class="text-xs font-semibold text-[#64748B] uppercase mt-0.5 truncate" title="Total Izin">Total Izin</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #FAF5FF; border: 1px solid #E9D5FF;">
                                <p class="text-2xl font-bold text-purple-700">{{ $sikStats['outside'] }}</p>
                                <p class="text-xs font-semibold text-purple-600 uppercase mt-0.5 truncate" title="Di Luar">Di Luar</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #F0FDF4; border: 1px solid #DCFCE7;">
                                <p class="text-2xl font-bold text-green-700">{{ $sikStats['returned'] }}</p>
                                <p class="text-xs font-semibold text-green-600 uppercase mt-0.5 truncate" title="Selesai">Selesai</p>
                            </div>
                            <div class="p-3 rounded-lg text-center min-w-0" style="background-color: #FFFBEB; border: 1px solid #FEF3C7;">
                                <p class="text-2xl font-bold text-amber-700">{{ $sikStats['pending'] }}</p>
                                <p class="text-xs font-semibold text-amber-600 uppercase mt-0.5 truncate" title="Menunggu">Menunggu</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Activity History Details -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg shadow-sm overflow-hidden">
        <!-- Tabs bar -->
        @php
            $hasMR = $user->isAdmin() || $user->hasModuleAccess('meeting_room') || $user->isGa() || $user->isIt();
            $hasTI = $user->isAdmin() || $user->hasModuleAccess('toilet_inspection');
            $hasATK = $user->isAdmin() || $user->hasModuleAccess('atk');
            $hasSIK = $user->isAdmin() || $user->hasModuleAccess('sik') || $user->isSecurity();
            
            $moduleCount = 0;
            if ($hasMR) $moduleCount++;
            if ($hasTI) $moduleCount++;
            if ($hasATK) $moduleCount++;
            if ($hasSIK) $moduleCount++;
            $showTabs = $moduleCount > 1;
        @endphp

        <div class="border-b border-[#E5E7EB] bg-[#F9FAFB] px-6 py-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-base font-bold text-[#0F172A]">Log Riwayat Aktivitas</h3>
            
            @if ($showTabs)
                <div class="flex rounded-md bg-[#E2E8F0] p-1 text-xs font-semibold text-[#64748B]">
                    <button type="button" onclick="switchTab('all')" id="tab-all" class="rounded px-3 py-1.5 bg-white text-[#0F172A] shadow-sm transition-all focus:outline-none">
                        Semua
                    </button>
                    @if ($hasMR)
                        <button type="button" onclick="switchTab('meeting_room')" id="tab-meeting_room" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                            Ruang Meeting
                        </button>
                    @endif
                    @if ($hasTI)
                        <button type="button" onclick="switchTab('toilet_inspection')" id="tab-toilet_inspection" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                            Inspeksi Toilet
                        </button>
                    @endif
                    @if ($hasATK)
                        <button type="button" onclick="switchTab('atk')" id="tab-atk" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                            ATK
                        </button>
                    @endif
                    @if ($hasSIK)
                        <button type="button" onclick="switchTab('sik')" id="tab-sik" class="rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none">
                            SIK
                        </button>
                    @endif
                </div>
            @endif
        </div>

        <div class="divide-y divide-[#E5E7EB]">
            <!-- MEETING ROOM HISTORY SECTION -->
            @if ($hasMR)
                <div id="section-meeting_room" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-[#2563EB] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">meeting_room</span> 
                        {{ $user->isAdmin() ? 'Pemesanan Ruang Meeting (Pemantauan)' : (($user->isGa() || $user->isIt()) ? 'Daftar Tugas Persiapan Ruang Meeting' : 'Pemesanan Ruang Meeting') }}
                    </h4>
                    
                    <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                        <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                            <thead class="bg-[#F8FAFC]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Ruangan</th>
                                    @if ($user->isAdmin() || $user->isGa() || $user->isIt())
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Dipesan Oleh</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Judul Pemesanan</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Slot Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                @forelse ($bookings as $booking)
                                    <tr class="hover:bg-[#F9FAFB] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                                            {{ $booking->booking_date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-[#2563EB]">
                                            {{ $booking->room ? $booking->room->name : 'Online' }}
                                        </td>
                                        @if ($user->isAdmin() || $user->isGa() || $user->isIt())
                                            <td class="px-6 py-4">
                                                {{ $booking->user->name }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 font-medium">{{ $booking->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                            {{ substr($booking->start_time, 0, 5) }} - {{ substr($booking->end_time, 0, 5) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                if ($user->isGa()) {
                                                    $taskStatus = $booking->ga_status;
                                                    $gaStyles = [
                                                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'in_progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                                        'ready' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'not_ready' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    ];
                                                    $gaLabels = [
                                                        'pending' => 'GA: Menunggu',
                                                        'in_progress' => 'GA: Dikerjakan',
                                                        'ready' => 'GA: Siap',
                                                        'not_ready' => 'GA: Tidak Siap',
                                                    ];
                                                    $bookingBadgeClass = $gaStyles[$taskStatus] ?? 'bg-slate-50 text-slate-700';
                                                    $labelText = $gaLabels[$taskStatus] ?? 'GA: Menunggu';
                                                } elseif ($user->isIt()) {
                                                    $taskStatus = $booking->it_status;
                                                    $itStyles = [
                                                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'in_progress' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                                        'ready' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'not_ready' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    ];
                                                    $itLabels = [
                                                        'pending' => 'IT: Menunggu',
                                                        'in_progress' => 'IT: Dikerjakan',
                                                        'ready' => 'IT: Siap',
                                                        'not_ready' => 'IT: Tidak Siap',
                                                    ];
                                                    $bookingBadgeClass = $itStyles[$taskStatus] ?? 'bg-slate-50 text-slate-700';
                                                    $labelText = $itLabels[$taskStatus] ?? 'IT: Menunggu';
                                                } else {
                                                    $bookingBadges = [
                                                        'pending' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                        'cancelled' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                        'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                    ];
                                                    $bookingBadgeClass = $bookingBadges[$booking->status] ?? 'bg-slate-50 text-slate-700';
                                                    $labelText = $booking->status === 'pending' ? 'Menunggu' : ($booking->status === 'approved' ? 'Disetujui' : ($booking->status === 'rejected' ? 'Ditolak' : ($booking->status === 'cancelled' ? 'Dibatalkan' : 'Selesai')));
                                                }
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium {{ $bookingBadgeClass }} ring-1 ring-inset">
                                                {{ $labelText }}
                                            </span>
                                            @if($booking->status === 'cancelled' && $booking->cancellation_note)
                                                <span class="text-[10px] text-slate-500 block italic max-w-xs mt-1">"{{ $booking->cancellation_note }}"</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                            Tidak ada data riwayat pemesanan yang ditemukan untuk pengguna ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if ($user->isAdmin() && $approvedBookings->isNotEmpty())
                        <div class="mt-6 space-y-3">
                            <h4 class="text-xs font-bold text-[#475569] uppercase tracking-wider">Pemesanan yang Disetujui oleh Admin ini</h4>
                            <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                                <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                                    <thead class="bg-[#F8FAFC]">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tanggal</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Ruangan</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Dipesan Oleh</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Judul Pemesanan</th>
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                        @foreach ($approvedBookings as $ab)
                                            <tr class="hover:bg-[#F9FAFB] transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $ab->booking_date->format('d M Y') }}</td>
                                                <td class="px-6 py-4 font-semibold">{{ $ab->room ? $ab->room->name : 'Online' }}</td>
                                                <td class="px-6 py-4">{{ $ab->user->name }}</td>
                                                <td class="px-6 py-4 font-medium">{{ $ab->title }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ substr($ab->start_time, 0, 5) }} - {{ substr($ab->end_time, 0, 5) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- TOILET INSPECTION HISTORY SECTION -->
            @if ($hasTI)
                <div id="section-toilet_inspection" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-[#D97706] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">wc</span> 
                        @if ($user->isAdmin())
                            Inspeksi Toilet (Pemantauan)
                        @elseif ($user->isSpv())
                            Persetujuan Inspeksi Toilet
                        @else
                            Inspeksi Toilet yang Dilakukan
                        @endif
                    </h4>
                    
                    <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                        <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                            <thead class="bg-[#F8FAFC]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Lokasi</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Bukti</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">
                                        @if ($user->isAdmin())
                                            Petugas Inspeksi
                                        @elseif ($user->isSpv())
                                            Petugas Inspeksi
                                        @else
                                            Ulasan / Persetujuan
                                        @endif
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                @if ($user->isSpv())
                                    @forelse ($approvals as $appr)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $appr->actioned_at ? $appr->actioned_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $appr->inspection->location->building }} &bull; {{ $appr->inspection->location->floor }} &bull; {{ $appr->inspection->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($appr->action === 'approve')
                                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Disetujui</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Ditolak</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-[#2563EB]">
                                                @if ($appr->inspection->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $appr->inspection->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> Lihat Foto
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">Tidak ada foto</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-medium">
                                                {{ $appr->inspection->inspector->name }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                Tidak ada ulasan persetujuan inspeksi yang ditemukan untuk supervisor ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                @elseif ($user->isAdmin())
                                    @forelse ($inspections as $ins)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $ins->inspected_at ? $ins->inspected_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $ins->location->building }} &bull; {{ $ins->location->floor }} &bull; {{ $ins->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $insBadges = [
                                                        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                        'draft' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    ];
                                                    $insBadgeClass = $insBadges[$ins->status] ?? 'bg-slate-50 text-slate-700';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $insBadgeClass }} ring-1 ring-inset">
                                                    {{ $ins->status === 'submitted' ? 'Menunggu Ulasan' : ($ins->status === 'approved' ? 'Disetujui' : ($ins->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($ins->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $ins->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> Lihat Foto
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">Tidak ada foto</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 font-medium">
                                                {{ $ins->inspector->name }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                Tidak ada inspeksi yang dipantau.
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse ($inspections as $ins)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium">
                                                {{ $ins->inspected_at ? $ins->inspected_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-[#D97706]">
                                                {{ $ins->location->building }} &bull; {{ $ins->location->floor }} &bull; {{ $ins->location->toilet_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $insBadges = [
                                                        'submitted' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                                                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                        'draft' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    ];
                                                    $insBadgeClass = $insBadges[$ins->status] ?? 'bg-slate-50 text-slate-700';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $insBadgeClass }} ring-1 ring-inset">
                                                    {{ $ins->status === 'submitted' ? 'Menunggu Ulasan' : ($ins->status === 'approved' ? 'Disetujui' : ($ins->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($ins->photo_path)
                                                    <button type="button" onclick="openLightbox('{{ asset('storage/' . $ins->photo_path) }}')" class="inline-flex items-center gap-1 font-semibold text-[#2563EB] hover:text-[#1D4ED8] underline cursor-pointer">
                                                        <span class="material-symbols-outlined text-[18px]">image</span> Lihat Foto
                                                    </button>
                                                @else
                                                    <span class="text-xs text-[#94A3B8] italic">Tidak ada foto</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-[#475569]">
                                                @if ($ins->approval)
                                                    <span class="font-medium text-[#0F172A]">{{ $ins->approval->action === 'approve' ? 'Disetujui' : 'Ditolak' }}</span>
                                                    oleh {{ $ins->approval->approver->name }} pada {{ $ins->approval->actioned_at->format('d M Y') }}
                                                @else
                                                    <span class="text-xs italic text-[#94A3B8]">Menunggu ulasan supervisor</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                                Belum ada inspeksi yang dilakukan.
                                            </td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- ATK HISTORY SECTION -->
            @if ($hasATK)
                <div id="section-atk" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-green-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">inventory_2</span> 
                        @if ($user->isAdmin())
                            Permintaan ATK (Pemantauan Sistem)
                        @elseif ($user->isGa())
                            Persetujuan/Pengeluaran ATK yang Diproses
                        @else
                            Riwayat Permintaan ATK
                        @endif
                    </h4>
                    
                    <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                        <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                            <thead class="bg-[#F8FAFC]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">No. Request</th>
                                    @if ($user->isAdmin() || $user->isGa())
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Pemohon</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Item Diminta</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E7EB] text-[#0F172A]">
                                @forelse ($atkRequests as $req)
                                    <tr class="hover:bg-[#F9FAFB] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium font-mono text-xs">
                                            {{ $req->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-green-700">
                                            {{ $req->request_number }}
                                        </td>
                                        @if ($user->isAdmin() || $user->isGa())
                                            <td class="px-6 py-4">
                                                {{ $req->user->name }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4">
                                            <div class="space-y-1">
                                                @foreach ($req->items as $item)
                                                    <p class="text-xs text-slate-700 font-medium">
                                                        {{ $item->variant->item->name }} &bull; {{ $item->variant->name }} 
                                                        <span class="text-[#2563EB] font-bold bg-blue-50 px-1.5 py-0.5 rounded ml-1">x{{ $item->quantity }} {{ $item->variant->unit }}</span>
                                                    </p>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $atkBadges = [
                                                    'diajukan' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                                    'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                    'diproses' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                                    'selesai' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                    'ditolak' => 'bg-red-50 text-red-700 ring-red-600/20',
                                                    'dibatalkan' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                ];
                                                $atkBadgeClass = $atkBadges[$req->status] ?? 'bg-slate-50 text-slate-700';
                                            @endphp
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $atkBadgeClass }} ring-1 ring-inset">
                                                {{ ucfirst($req->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $user->isAdmin() || $user->isGa() ? 5 : 4 }}" class="px-6 py-8 text-center text-[#94A3B8] italic bg-white">
                                            Tidak ada riwayat permintaan ATK ditemukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- SIK HISTORY SECTION -->
            @if ($hasSIK)
                <div id="section-sik" class="tab-section p-6 space-y-4">
                    <h4 class="text-sm font-bold text-[#7C3AED] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">badge</span> 
                        @if ($user->isAdmin())
                            Surat Izin Keluar (Pemantauan Seluruh Sistem)
                        @elseif ($user->isSecurity())
                            Log Scan Gerbang Security
                        @elseif ($user->isSikApprover())
                            Evaluasi Persetujuan SIK
                        @else
                            Riwayat Pengajuan Surat Izin Keluar (SIK)
                        @endif
                    </h4>
                    
                    @if ($user->isSecurity())
                        <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                            <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                                <thead class="bg-[#F8FAFC]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Waktu Scan</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Nomor SIK</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Pemohon</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tipe Scan</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Gerbang</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($sikScans as $scan)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-[#475569] font-mono text-xs">
                                                {{ $scan->scanned_at ? $scan->scanned_at->format('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-[#2563EB]">
                                                {{ $scan->exitPermit->sik_number ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-[#0F172A] font-medium">
                                                {{ $scan->exitPermit->user->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase {{ $scan->scan_type === 'EXIT' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $scan->scan_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                                {{ $scan->gate ?: 'Pos Gerbang' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-sm text-[#94A3B8] italic bg-white">
                                                Belum ada riwayat scan gerbang yang tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="overflow-x-auto border border-[#E5E7EB] rounded-lg">
                            <table class="min-w-full divide-y divide-[#E5E7EB] text-sm text-left">
                                <thead class="bg-[#F8FAFC]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Nomor SIK</th>
                                        @if ($user->isAdmin() || $user->isSikApprover())
                                            <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Pemohon</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Keperluan</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Tanggal & Jam</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase">Status</th>
                                        <th scope="col" class="px-6 py-3 text-xs font-semibold text-[#475569] uppercase text-right">Detail</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#E5E7EB] bg-white">
                                    @forelse ($sikPermits as $permit)
                                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-[#2563EB]">
                                                {{ $permit->sik_number }}
                                            </td>
                                            @if ($user->isAdmin() || $user->isSikApprover())
                                                <td class="px-6 py-4 whitespace-nowrap text-[#0F172A] font-medium">
                                                    {{ $permit->user->name ?? '-' }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 text-[#475569] max-w-xs truncate">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $permit->purpose_type === 'dinas' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700' }} mr-1">
                                                    {{ $permit->purpose_type }}
                                                </span>
                                                {{ $permit->purpose }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                                {{ $permit->permit_date ? $permit->permit_date->format('d/m/Y') : '-' }} ({{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }})
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php $eff = $permit->effective_status; @endphp
                                                @if ($eff === 'draft')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700">DRAFT</span>
                                                @elseif ($eff === 'pending_approval')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">MENUNGGU</span>
                                                @elseif ($eff === 'approved')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">DISETUJUI</span>
                                                @elseif ($eff === 'outside')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800">DI LUAR</span>
                                                @elseif ($eff === 'returned')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">SELESAI</span>
                                                @elseif ($eff === 'rejected')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">DITOLAK</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-800">{{ strtoupper($eff) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                                <a href="{{ route('sik.show', $permit) }}" class="text-[#2563EB] hover:underline font-semibold">
                                                    Lihat SIK
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ ($user->isAdmin() || $user->isSikApprover()) ? 6 : 5 }}" class="px-6 py-8 text-center text-sm text-[#94A3B8] italic bg-white">
                                                Belum ada permohonan surat izin keluar.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Photo Evidence Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 cursor-pointer" onclick="closeLightbox()">
    <div class="max-w-4xl max-h-full relative flex flex-col justify-center items-center">
        <img id="lightbox-img" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" src="" alt="Enlarged Evidence">
        <p class="text-white text-xs mt-3 bg-black/40 px-3 py-1.5 rounded-full">Klik di mana saja untuk menutup</p>
    </div>
</div>

<!-- Tab and Lightbox Scripts -->
<script>
    function switchTab(moduleName) {
        // Toggle Active styles on buttons
        const tabAll = document.getElementById('tab-all');
        const tabMR = document.getElementById('tab-meeting_room');
        const tabTI = document.getElementById('tab-toilet_inspection');
        const tabATK = document.getElementById('tab-atk');
        const tabSIK = document.getElementById('tab-sik');
        
        const secMR = document.getElementById('section-meeting_room');
        const secTI = document.getElementById('section-toilet_inspection');
        const secATK = document.getElementById('section-atk');
        const secSIK = document.getElementById('section-sik');

        // Reset buttons to non-active styles
        [tabAll, tabMR, tabTI, tabATK, tabSIK].forEach(btn => {
            if (btn) {
                btn.className = 'rounded px-3 py-1.5 transition-all hover:text-[#0F172A] focus:outline-none';
            }
        });

        // Set active style to clicked button
        const activeBtn = document.getElementById('tab-' + moduleName);
        if (activeBtn) {
            activeBtn.className = 'rounded px-3 py-1.5 bg-white text-[#0F172A] shadow-sm transition-all focus:outline-none';
        }

        // Show/hide sections
        if (moduleName === 'all') {
            if (secMR) secMR.classList.remove('hidden');
            if (secTI) secTI.classList.remove('hidden');
            if (secATK) secATK.classList.remove('hidden');
            if (secSIK) secSIK.classList.remove('hidden');
        } else if (moduleName === 'meeting_room') {
            if (secMR) secMR.classList.remove('hidden');
            if (secTI) secTI.classList.add('hidden');
            if (secATK) secATK.classList.add('hidden');
            if (secSIK) secSIK.classList.add('hidden');
        } else if (moduleName === 'toilet_inspection') {
            if (secMR) secMR.classList.add('hidden');
            if (secTI) secTI.classList.remove('hidden');
            if (secATK) secATK.classList.add('hidden');
            if (secSIK) secSIK.classList.add('hidden');
        } else if (moduleName === 'atk') {
            if (secMR) secMR.classList.add('hidden');
            if (secTI) secTI.classList.add('hidden');
            if (secATK) secATK.classList.remove('hidden');
            if (secSIK) secSIK.classList.add('hidden');
        } else if (moduleName === 'sik') {
            if (secMR) secMR.classList.add('hidden');
            if (secTI) secTI.classList.add('hidden');
            if (secATK) secATK.classList.add('hidden');
            if (secSIK) secSIK.classList.remove('hidden');
        }
    }

    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.getElementById('lightbox-img').src = '';
    }
</script>
@endsection
