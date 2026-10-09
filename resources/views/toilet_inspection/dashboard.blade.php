@extends('layouts.app')

@section('title', 'Dashboard Inspeksi Toilet - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Dashboard Inspeksi Toilet
                </h1>
                <p class="text-xs sm:text-sm text-[#475569] mt-1">
                    Selamat datang kembali, {{ auth()->user()->name }} (Role: {{ auth()->user()->role === 'admin' ? 'Admin' : (auth()->user()->role === 'spv' ? 'SPV' : (auth()->user()->role === 'ga' ? 'GA' : 'Petugas Inspeksi')) }})
                </p>
            </div>
            @if(!auth()->user()->isAdmin() && !auth()->user()->isSpv() && !auth()->user()->isGa())
                <a href="{{ route('inspections.create') }}" class="rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all">
                    + Inspeksi Baru
                </a>
            @endif
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-xl bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="mb-6 rounded-xl bg-amber-50 p-4 border border-amber-200 text-sm font-medium text-amber-800">
                {{ session('error') }}
            </div>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isSpv() || auth()->user()->isGa())
            <!-- Period Selector Form -->
            <form method="GET" action="{{ route('toilet_inspection.dashboard') }}" class="flex flex-wrap gap-4 items-center mb-6 bg-white border border-[#E5E7EB] p-4 rounded-xl shadow-sm" onsubmit="sessionStorage.setItem('toilet_inspection_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <div>
                    <label for="date_filter" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Periode</label>
                    <select name="date_filter" id="date_filter" onchange="sessionStorage.setItem('toilet_inspection_dashboard_scroll', document.querySelector('main')?.scrollTop || 0); toggleCustomDates(); this.form.submit();"
                        class="rounded-md border border-[#E5E7EB] bg-white px-3 py-1.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="daily" {{ $dateFilter === 'daily' || $dateFilter === 'today' ? 'selected' : '' }}>Harian</option>
                        <option value="weekly" {{ $dateFilter === 'weekly' || $dateFilter === 'this_week' ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly" {{ $dateFilter === 'monthly' || $dateFilter === 'this_month' ? 'selected' : '' }}>Bulanan</option>
                        <option value="yearly" {{ $dateFilter === 'yearly' || $dateFilter === 'this_year' ? 'selected' : '' }}>Tahunan</option>
                        <option value="custom" {{ $dateFilter === 'custom' ? 'selected' : '' }}>Rentang Tanggal Kustom</option>
                    </select>
                </div>
                
                <div class="flex flex-wrap gap-4 {{ request('date_filter') === 'custom' ? '' : 'hidden' }}" id="custom-dates">
                    <div>
                        <label for="start_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                            class="rounded-md border border-[#E5E7EB] px-3 py-1 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                    <div>
                        <label for="end_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                            class="rounded-md border border-[#E5E7EB] px-3 py-1 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                    <div class="self-end pb-0.5">
                        <button type="submit" class="rounded bg-[#2563EB] px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all">
                            Terapkan
                        </button>
                    </div>
                </div>
            </form>
        @endif

        <!-- Stats Widgets -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4 mb-8">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">analytics</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Total Inspeksi">Total Inspeksi</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $totalInspections }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">pending_actions</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Menunggu Ulasan">Menunggu Ulasan</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $pendingCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#F0FDF4] text-[#16A34A] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">check_circle</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Disetujui">Disetujui</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $approvedCount }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-lg bg-[#FEF2F2] text-[#DC2626] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[24px]">cancel</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Ditolak">Ditolak</p>
                    <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $rejectedCount }}</p>
                </div>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isSpv() || auth()->user()->isGa())
                <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0 col-span-2 sm:col-span-2 md:col-span-1 xl:col-span-1">
                    <div class="p-2.5 sm:p-3 rounded-lg bg-[#FAF5FF] text-[#7C3AED] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px] sm:text-[24px]">percent</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Tingkat Persetujuan">Tingkat Persetujuan</p>
                        <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ isset($approvalRate) ? $approvalRate : 0 }}%</p>
                    </div>
                </div>
            @else
                <div class="bg-white border border-[#E5E7EB] rounded-xl p-3.5 sm:p-4 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0 col-span-2 sm:col-span-2 md:col-span-1 xl:col-span-1">
                    <div class="p-2.5 sm:p-3 rounded-lg bg-[#F8FAFC] text-[#64748B] flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px] sm:text-[24px]">person</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] sm:text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Role Saya">Role Saya</p>
                        <p class="text-sm sm:text-base font-bold text-[#0F172A] mt-0.5 truncate" title="Petugas Inspeksi">Petugas Inspeksi</p>
                    </div>
                </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isSpv() || auth()->user()->isGa())
            @if($totalInspections === 0)
                <!-- Empty State Message for selected period -->
                <div class="rounded-lg border border-[#E5E7EB] bg-white p-12 text-center text-[#64748B] mb-8">
                    <span class="material-symbols-outlined text-[48px] text-[#94A3B8]">analytics</span>
                    <p class="mt-2 font-semibold">Tidak ada inspeksi yang tercatat untuk periode yang dipilih.</p>
                    <p class="text-xs text-[#94A3B8] mt-1">Coba pilih rentang waktu lain atau hapus filter.</p>
                </div>
            @else
                <!-- Charts Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Status Chart -->
                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm flex flex-col justify-between lg:col-span-1">
                        <h3 class="text-sm font-bold text-[#0F172A] mb-4">Status Inspeksi</h3>
                        <div class="w-full max-w-[200px] mx-auto aspect-square relative flex items-center justify-center">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Trend Chart -->
                    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm flex flex-col justify-between lg:col-span-2">
                        <h3 class="text-sm font-bold text-[#0F172A] mb-4">
                            Tren Inspeksi ({{
                                $dateFilter === 'today' || $dateFilter === 'daily' ? 'Harian' : (
                                $dateFilter === 'this_week' || $dateFilter === 'weekly' ? 'Mingguan' : (
                                $dateFilter === 'this_year' || $dateFilter === 'yearly' ? 'Tahunan' : (
                                $dateFilter === 'custom' ? 'Rentang Tanggal Kustom' : 'Bulanan'
                            )))
                            }})
                        </h3>
                        <div class="w-full h-full min-h-[200px] relative">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Location Summary Section -->
                <div class="bg-white border border-[#E5E7EB] rounded-lg shadow-sm mb-8 overflow-hidden">
                    <div class="px-6 py-5 border-b border-[#E5E7EB]">
                        <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Ringkasan Lokasi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#E5E7EB]">
                            <thead class="bg-[#F9FAFB]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gedung</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lantai</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Inspeksi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Disetujui</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Ditolak</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tingkat Persetujuan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                                @forelse($locationSummary as $loc)
                                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $loc->building }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->floor }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->total }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->approved }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $loc->rejected }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                                            {{ $loc->total > 0 ? round(($loc->approved / $loc->total) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-[#64748B]">Tidak ada data lokasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isGa())
            <!-- Admin Logs -->
            <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                <div class="px-6 py-5 border-b border-[#E5E7EB]">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Inspeksi Terbaru</h3>
                </div>
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($recentInspections as $ins)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                    {{ $ins->location->building }}, {{ $ins->location->floor }} — Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $ins->location->toilet_name }})
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badges = [
                                            'submitted' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                            'approved' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                                            'rejected' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                                            'draft' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                                        ];
                                        $badgeClass = $badges[$ins->status] ?? $badges['draft'];
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $badgeClass }} ring-1 ring-inset">
                                        {{ $ins->status === 'submitted' ? 'Menunggu Ulasan' : ($ins->status === 'approved' ? 'Disetujui' : ($ins->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-[#64748B]">Tidak ada inspeksi terbaru yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif(auth()->user()->isSpv())
            <!-- SPV Approvals List -->
            <div class="space-y-6">
                <!-- Pending Reviews -->
                <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                    <div class="px-6 py-5 border-b border-[#E5E7EB]">
                        <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Menunggu Ulasan</h3>
                    </div>
                    <table class="min-w-full divide-y divide-[#E5E7EB]">
                        <thead class="bg-[#F9FAFB]">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                            @forelse ($pendingInspections as $ins)
                                <tr class="hover:bg-[#F8FAFC] transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                        {{ $ins->location->building }}, {{ $ins->location->floor }} — Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $ins->location->toilet_name }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                        <a href="{{ route('toilet_inspection.review', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                            Ulas
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">Semua bersih! Tidak ada ulasan yang tertunda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <!-- Inspector/User History -->
            <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
                <div class="px-6 py-5 border-b border-[#E5E7EB]">
                    <h3 class="text-base font-semibold leading-6 text-[#0F172A]">Log Inspeksi Saya</h3>
                </div>
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($myInspections as $ins)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-medium text-[#0F172A]">
                                    {{ $ins->location->building }}, {{ $ins->location->floor }} — Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $ins->location->toilet_name }})
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $badges = [
                                            'submitted' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                            'approved' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                                            'rejected' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                                            'draft' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                                        ];
                                        $badgeClass = $badges[$ins->status] ?? $badges['draft'];
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $badgeClass }} ring-1 ring-inset">
                                        {{ $ins->status === 'submitted' ? 'Menunggu Ulasan' : ($ins->status === 'approved' ? 'Disetujui' : ($ins->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                                    <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">Anda belum mengirimkan inspeksi apa pun.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </main>
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isSpv() || auth()->user()->isGa())
    <script>
        function toggleCustomDates() {
            const select = document.getElementById('date_filter');
            const customDatesDiv = document.getElementById('custom-dates');
            if (select.value === 'custom') {
                customDatesDiv.classList.remove('hidden');
            } else {
                customDatesDiv.classList.add('hidden');
            }
        }
        
        // Run toggle on page load to restore state if customized
        function initToiletDashboard() {
            toggleCustomDates();

            // Restore scroll position
            const scrollPos = sessionStorage.getItem('toilet_inspection_dashboard_scroll');
            if (scrollPos) {
                const mainEl = document.querySelector('main');
                if (mainEl) {
                    mainEl.scrollTop = parseInt(scrollPos);
                    setTimeout(() => {
                        mainEl.scrollTop = parseInt(scrollPos);
                    }, 50);
                    setTimeout(() => {
                        mainEl.scrollTop = parseInt(scrollPos);
                    }, 150);
                    setTimeout(() => {
                        mainEl.scrollTop = parseInt(scrollPos);
                        sessionStorage.removeItem('toilet_inspection_dashboard_scroll');
                    }, 300);
                }
            }
        }

        if (window.onPageLoad) {
            window.onPageLoad(initToiletDashboard);
        } else {
            document.addEventListener('DOMContentLoaded', initToiletDashboard);
        }
    </script>

    @if($totalInspections > 0)
        <script src="{{ asset('js/chart.umd.js') }}"></script>
        <script>
            function initToiletDashboardCharts() {
                // Status Chart
                const statusCanvas = document.getElementById('statusChart');
                if (statusCanvas) {
                    const statusCtx = statusCanvas.getContext('2d');
                    if (window.myToiletStatusChart) { window.myToiletStatusChart.destroy(); }
                    window.myToiletStatusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Menunggu Ulasan', 'Disetujui', 'Ditolak'],
                            datasets: [{
                                data: [{{ $pendingCount }}, {{ $approvedCount }}, {{ $rejectedCount }}],
                                backgroundColor: ['#FBBF24', '#16A34A', '#DC2626'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10,
                                        font: { size: 10, family: 'Inter' }
                                    }
                                }
                            }
                        }
                    });
                }

                // Trend Chart
                @php
                    $trendLabels = array_keys($trendData);
                    $trendValues = array_values($trendData);
                @endphp
                const trendCanvas = document.getElementById('trendChart');
                if (trendCanvas) {
                    const trendCtx = trendCanvas.getContext('2d');
                    if (window.myToiletTrendChart) { window.myToiletTrendChart.destroy(); }
                    window.myToiletTrendChart = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($trendLabels) !!},
                            datasets: [{
                                label: 'Jumlah Inspeksi',
                                data: {!! json_encode($trendValues) !!},
                                borderColor: '#2563EB',
                                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                                fill: true,
                                tension: 0.25,
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        font: { size: 10, family: 'Inter' }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: { size: 10, family: 'Inter' }
                                    }
                                }
                            }
                        }
                    });
                }
            }
            if (window.onPageLoad) { window.onPageLoad(initToiletDashboardCharts); } else { document.addEventListener('DOMContentLoaded', initToiletDashboardCharts); }
        </script>
    @endif
@endif
@endsection
