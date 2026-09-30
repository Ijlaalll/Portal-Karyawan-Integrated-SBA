@extends('layouts.app')

@section('title', 'Dashboard Admin - SBA')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    
    <!-- Header with Actions and Period Filter -->
    <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-y-4 border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0F172A]">
                Dashboard Ruang Meeting
            </h1>
            <p class="text-sm text-[#475569] mt-1">Selamat datang kembali, {{ auth()->user()->name }} | Detail performa modul, metrik ruangan, rating, dan ulasan.</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-4">
            <!-- Period Selector Form -->
            <form action="{{ route('admin.dashboard') }}" method="GET" id="dashboardFilterForm" class="flex flex-wrap items-center gap-2" onsubmit="sessionStorage.setItem('meeting_room_dashboard_scroll', window.scrollY);">
                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg mr-1 inline-block">
                    @if($period === 'yearly')
                        {{ $start->format('Y') }}
                    @elseif($period === 'monthly')
                        {{ $start->format('F Y') }}
                    @elseif($period === 'weekly')
                        {{ $start->format('d M') }} - {{ $end->format('d M Y') }}
                    @else
                        {{ $start->format('d M Y') }}
                    @endif
                </span>

                <div class="inline-flex max-w-full overflow-x-auto rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold">
                    <button type="button" onclick="setDashboardPeriod('daily')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setDashboardPeriod('weekly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setDashboardPeriod('monthly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setDashboardPeriod('yearly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="period" id="dashboard_period_input" value="{{ $period }}">

                <div>
                    @if($period === 'yearly')
                        <select name="date" onchange="submitFormWithTurbo(this)" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $dateInput == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($period === 'monthly')
                        <input type="month" name="date" value="{{ $dateInput }}" onchange="submitFormWithTurbo(this)" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="date" value="{{ $dateInput }}" onchange="submitFormWithTurbo(this)" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-x-2">
                <a href="{{ route('admin.facilities.create') }}" class="rounded-lg bg-white px-3.5 py-2 text-xs font-bold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB] flex items-center transition-colors">
                    Tambah Fasilitas
                </a>
            </div>
        </div>
    </header>

    <main class="space-y-8">
        
        <!-- Metrics Grid Container (Compact 6-Column Layout) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4">
            
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-blue-500">
                <span class="text-[9px] font-bold text-[#64748B] uppercase tracking-wider block">TOTAL PEMESANAN</span>
                <span class="text-xl font-extrabold text-[#0F172A] leading-none mt-1">{{ $stats['total_bookings'] }}</span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Dalam periode terpilih</span>
            </div>
 
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-green-500">
                <span class="text-[9px] font-bold text-green-700 uppercase tracking-wider block">DISETUJUI</span>
                <span class="text-xl font-extrabold text-green-950 leading-none mt-1">{{ $stats['approved_bookings'] }}</span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Pemesanan dikonfirmasi</span>
            </div>
 
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-red-500">
                <span class="text-[9px] font-bold text-red-700 uppercase tracking-wider block">DITOLAK / DIBATALKAN</span>
                <span class="text-xl font-extrabold text-red-950 leading-none mt-1">{{ $stats['rejected_bookings'] + $stats['cancelled_bookings'] }}</span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Ditolak atau dibatalkan</span>
            </div>
 
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-amber-500">
                <span class="text-[9px] font-bold text-amber-800 uppercase tracking-wider block">RATING RATA-RATA</span>
                <span class="text-xl font-extrabold text-[#0F172A] leading-none mt-1">
                    ★ {{ $stats['average_rating'] > 0 ? number_format($stats['average_rating'], 1) : 'N/A' }}
                </span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Dari 5 bintang</span>
            </div>
 
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-purple-500">
                <span class="text-[9px] font-bold text-purple-700 uppercase tracking-wider block">TOTAL ULASAN</span>
                <span class="text-xl font-extrabold text-[#0F172A] leading-none mt-1">{{ $stats['total_reviews'] }}</span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Rating & komentar</span>
            </div>
 
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-3 h-[88px] flex flex-col justify-between shadow-sm border-l-4 border-l-green-500">
                <span class="text-[9px] font-bold text-green-700 uppercase tracking-wider block">RUANGAN BEROPERASI</span>
                <span class="text-xl font-extrabold text-[#0F172A] leading-none mt-1">
                    {{ $stats['active_rooms'] }} / {{ $stats['total_rooms'] }}
                </span>
                <span class="text-[10px] text-slate-400 font-semibold block mt-1">Ruangan aktif</span>
            </div>

        </div>

        <!-- Trend Line Chart -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-[#475569] uppercase tracking-wider">Trend Penggunaan Meeting Room</h3>
            <div class="relative h-[280px]">
                @if($stats['total_bookings'] > 0)
                    <div class="h-[280px]">
                        <canvas id="dashboardTrendChart"></canvas>
                    </div>
                @else
                    <div class="h-[280px] w-full flex flex-col items-center justify-center text-center text-slate-400 bg-slate-50 border border-slate-100 rounded-xl p-6">
                        <span class="material-symbols-outlined text-[40px] text-slate-300 mb-2">analytics</span>
                        <h4 class="text-xs font-bold text-slate-600">Tidak ada data pemesanan yang tersedia untuk periode ini.</h4>
                        <p class="text-[10px] text-slate-400 mt-1 max-w-[200px]">Tidak ada pemesanan yang tercatat untuk periode terpilih.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Room Utilization & Performance Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Room Utilization -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm p-6 space-y-5">
                <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider border-b border-slate-100 pb-3">Pemanfaatan Ruangan</h3>
                <div class="space-y-4">
                    @forelse($roomPerformance as $room)
                        @php
                            $utilization = $stats['total_bookings'] > 0 ? round(($room['bookings_count'] / $stats['total_bookings']) * 100) : 0;
                        @endphp
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700">
                                <span class="truncate w-[60%]" title="{{ $room['name'] }}">{{ $room['name'] }}</span>
                                <span>{{ $room['bookings_count'] }} pemesanan ({{ $utilization }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-[#2563EB] h-2 rounded-full transition-all duration-500" style="width: {{ $utilization }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-xs text-slate-400 italic">Tidak ada riwayat penggunaan.</div>
                    @endforelse
                </div>
            </div>

            <!-- Room Rating Performance -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm p-6 flex flex-col justify-between">
                <div>
                    <div class="border-b border-slate-100 pb-3">
                        <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider">Performa Ruangan</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                        @forelse(collect($roomPerformance)->take(3) as $room)
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col justify-between h-[88px] hover:border-blue-100 hover:bg-blue-50/5 transition-all shadow-sm">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-extrabold text-[#0F172A] truncate w-[70%]" title="{{ $room['name'] }}">
                                        {{ $room['name'] }}
                                    </span>
                                    <span class="text-xs font-bold text-[#2563EB] flex items-center gap-0.5 bg-blue-50/50 px-2 py-0.5 rounded shrink-0">
                                        <span class="material-symbols-outlined text-[14px] text-amber-500" style="font-variation-settings: 'FILL' 1;">star</span>
                                        {{ $room['average_rating'] !== null ? number_format($room['average_rating'], 1) : 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-[#64748B] font-semibold mt-2 pt-2 border-t border-slate-100/50">
                                    <span class="truncate w-[65%]" title="{{ $room['location'] }}">{{ $room['location'] }}</span>
                                    <span class="text-[#2563EB] bg-blue-50/30 px-1.5 py-0.5 rounded shrink-0">{{ $room['reviews_count'] }} ulasan</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 text-center py-12 text-xs text-slate-400 italic">Tidak ada rating.</div>
                        @endforelse
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 mt-6">
                    <a href="{{ route('admin.room_performance') }}" class="w-full text-center block bg-slate-50 border border-slate-200 hover:border-[#2563EB] text-[#2563EB] font-bold text-xs py-2.5 px-4 rounded-lg transition-all shadow-sm">
                        Lihat Semua Performa Ruangan
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Usage Table & Recent Reviews Feed Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Side: Recent Room Usage (Span 2) -->
            <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-xl shadow-sm p-6 flex flex-col h-[520px]">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                    <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider">Penggunaan Ruangan Terbaru</h3>
                    <a href="{{ route('admin.booking_history') }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                        Lihat Riwayat Penggunaan Ruangan
                    </a>
                </div>
                <div class="overflow-x-auto flex-1 mt-4">
                    <table class="min-w-full divide-y divide-[#E5E7EB] text-left">
                        <thead>
                            <tr class="bg-[#F9FAFB] text-xs font-bold text-[#475569] uppercase tracking-wider">
                                <th class="px-4 py-3">Pengguna</th>
                                <th class="px-4 py-3">Ruangan</th>
                                <th class="px-4 py-3">Meeting / Judul</th>
                                <th class="px-4 py-3">Tanggal & Waktu</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#0F172A]">
                            @forelse($recentBookings as $b)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="px-4 py-3.5 font-bold">{{ $b->user->name }}</td>
                                    <td class="px-4 py-3.5 text-[#2563EB] font-bold">{{ $b->room ? $b->room->name : 'Online' }}</td>
                                    <td class="px-4 py-3.5 max-w-[120px] truncate" title="{{ $b->title }}">{{ $b->title }}</td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        <div>{{ $b->booking_date->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @php
                                            $statusStyles = [
                                                'pending' => 'background-color: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;',
                                                'approved' => 'background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;',
                                                'rejected' => 'background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;',
                                                'cancelled' => 'background-color: #F8FAFC; color: #64748B; border: 1px solid #CBD5E1;',
                                                'completed' => 'background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;',
                                            ];
                                            $statusStyle = $statusStyles[$b->status] ?? 'background-color: #F8FAFC; color: #64748B; border: 1px solid #CBD5E1;';
                                        @endphp
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold" style="{{ $statusStyle }}">
                                            {{ $b->status === 'pending' ? 'Menunggu' : ($b->status === 'approved' ? 'Disetujui' : ($b->status === 'rejected' ? 'Ditolak' : ($b->status === 'cancelled' ? 'Dibatalkan' : 'Selesai'))) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-semibold">
                                        <div class="flex items-center justify-end">
                                            <a href="{{ route('bookings.show', $b) }}" 
                                                class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold hover:opacity-80 transition-opacity"
                                                style="background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                                                <span class="material-symbols-outlined text-[14px]">visibility</span>
                                                Lihat
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-12 text-slate-400 italic">Tidak ada pemesanan yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side: Recent Room Reviews (Span 1) -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm p-6 flex flex-col h-[520px]">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                    <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider">Ulasan Ruangan Terbaru</h3>
                    <a href="{{ route('admin.reviews') }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                        Lihat Semua Ulasan
                    </a>
                </div>
                <div class="overflow-y-auto flex-1 mt-4 space-y-4 pr-1">
                    @forelse ($recentReviews as $review)
                        <div class="border border-slate-100 rounded-xl p-4 bg-slate-50/50 space-y-3 hover:border-slate-200 transition-all">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="material-symbols-outlined text-[16px] {{ $i <= $review->rating ? 'text-amber-500' : 'text-slate-300' }}" {!! $i <= $review->rating ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>star</span>
                                    @endfor
                                    <span class="text-xs font-bold text-[#0F172A] ml-1">{{ number_format($review->rating, 1) }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold">{{ $review->booking_date->format('d M Y') }}</span>
                            </div>
                            <blockquote class="text-xs text-slate-700 italic border-l-2 border-[#2563EB] pl-3 py-0.5">
                                "{{ $review->feedback ?: 'Tidak ada feedback tertulis yang diberikan.' }}"
                            </blockquote>
                            <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium">
                                <span class="text-[#2563EB] font-bold">{{ $review->room ? $review->room->name : 'Online' }}</span>
                                <span>oleh {{ $review->user->name }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center text-slate-400">
                            <span class="material-symbols-outlined text-[36px] text-slate-300 mb-2">rate_review</span>
                            <p class="text-xs italic">Tidak ada ulasan pengguna yang dikirimkan pada periode ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </main>
</div>

<!-- scripts integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function setDashboardPeriod(val) {
        sessionStorage.setItem('meeting_room_dashboard_scroll', window.scrollY);
        document.getElementById('dashboard_period_input').value = val;
        const now = new Date();
        let targetUrl = '';
        if (val === 'yearly') {
            targetUrl = `{{ route('admin.dashboard') }}?period=yearly&date=${now.getFullYear()}` + `&ti_period={{ request('ti_period') }}&ti_date={{ request('ti_date') }}`;
        } else if (val === 'monthly') {
            const monthStr = now.toISOString().slice(0, 7); // YYYY-MM
            targetUrl = `{{ route('admin.dashboard') }}?period=monthly&date=${monthStr}` + `&ti_period={{ request('ti_period') }}&ti_date={{ request('ti_date') }}`;
        } else {
            const dayStr = now.toISOString().slice(0, 10); // YYYY-MM-DD
            targetUrl = `{{ route('admin.dashboard') }}?period=${val}&date=${dayStr}` + `&ti_period={{ request('ti_period') }}&ti_date={{ request('ti_date') }}`;
        }
        if (window.navigateTo) { window.navigateTo(targetUrl); } else { window.location.href = targetUrl; }
    }

    function initMeetingRoomDashboard() {
        // Restore scroll position
        const scrollPos = sessionStorage.getItem('meeting_room_dashboard_scroll');
        if (scrollPos) {
            window.scrollTo(0, parseInt(scrollPos));
            sessionStorage.removeItem('meeting_room_dashboard_scroll');
        }

        @if($stats['total_bookings'] > 0)
            const trendCanvas = document.getElementById('dashboardTrendChart');
            if (trendCanvas) {
                const trendCtx = trendCanvas.getContext('2d');
                if (window.myTrendChartInstance) { window.myTrendChartInstance.destroy(); }
                window.myTrendChartInstance = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($trendLabels) !!},
                        datasets: [{
                            label: 'Volume Pemesanan',
                            data: {!! json_encode($trendData) !!},
                            borderColor: '#2563EB',
                            backgroundColor: 'rgba(37, 99, 235, 0.05)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 }
                            }
                        }
                    }
                });
            }
        @endif
    }
    if (window.onPageLoad) { window.onPageLoad(initMeetingRoomDashboard); } else { document.addEventListener('DOMContentLoaded', initMeetingRoomDashboard); }
</script>
@endsection
