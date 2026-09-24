@extends('layouts.app')

@section('title', 'Performa Ruangan - SBA')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4 border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0F172A]">
                Detail Performa Ruangan
            </h1>
            <p class="text-sm text-[#475569] mt-1">Agregat statistik, frekuensi penggunaan, dan rating pengguna untuk semua ruangan.</p>
        </div>
        <div class="flex items-center gap-4">
            <!-- Period Selector Form -->
            <form action="{{ route('admin.room_performance') }}" method="GET" id="performanceFilterForm" class="flex items-center gap-2" onsubmit="sessionStorage.setItem('performance_page_scroll', document.querySelector('main')?.scrollTop || 0);">
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

                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold">
                    <button type="button" onclick="setPerformancePeriod('daily')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setPerformancePeriod('weekly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setPerformancePeriod('monthly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setPerformancePeriod('yearly')" class="px-3 py-1.5 rounded-md transition-all {{ $period === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="period" id="performance_period_input" value="{{ $period }}">

                <div>
                    @if($period === 'yearly')
                        <select name="date" onchange="sessionStorage.setItem('performance_page_scroll', document.querySelector('main')?.scrollTop || 0); this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $dateInput == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($period === 'monthly')
                        <input type="month" name="date" value="{{ $dateInput }}" onchange="sessionStorage.setItem('performance_page_scroll', document.querySelector('main')?.scrollTop || 0); this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="date" value="{{ $dateInput }}" onchange="sessionStorage.setItem('performance_page_scroll', document.querySelector('main')?.scrollTop || 0); this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-white border border-[#E5E7EB] px-4 py-2 text-xs font-bold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Kembali
            </a>
        </div>
    </header>

    <!-- Performance Table/List -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#E5E7EB] bg-slate-50 text-[11px] font-bold text-[#475569] uppercase tracking-wider">
                        <th class="px-6 py-4">Ruangan & Lokasi</th>
                        <th class="px-6 py-4 text-center">Jumlah Penggunaan</th>
                        <th class="px-6 py-4 text-center">Pemanfaatan</th>
                        <th class="px-6 py-4 text-center">Rating Rata-Rata</th>
                        <th class="px-6 py-4 text-center">Total Ulasan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#0F172A] font-semibold">
                    @forelse($roomPerformance as $room)
                        @php
                            $utilization = $totalBookings > 0 ? round(($room['bookings_count'] / $totalBookings) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-extrabold text-slate-800 text-sm">{{ $room['name'] }}</div>
                                <div class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $room['location'] }}</div>
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-bold text-slate-700">
                                {{ $room['bookings_count'] }} pemesanan
                            </td>
                            <td class="px-6 py-4">
                                <div class="max-w-[120px] mx-auto space-y-1">
                                    <div class="text-right text-[10px] font-bold text-slate-500">{{ $utilization }}%</div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-full bg-[#2563EB]" style="width: {{ $utilization }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($room['average_rating'] !== null)
                                    <div class="inline-flex items-center gap-0.5 bg-amber-50 text-amber-700 font-extrabold px-2.5 py-1 rounded">
                                        <span class="material-symbols-outlined text-[14px] fill-amber-500 text-amber-500">star</span>
                                        {{ number_format($room['average_rating'], 1) }}
                                    </div>
                                @else
                                    <span class="text-slate-400 font-normal italic">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center text-slate-500 font-medium">
                                {{ $room['reviews_count'] }} ulasan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic bg-white">
                                <span class="material-symbols-outlined text-[48px] text-slate-300 mb-2 block font-normal">meeting_room</span>
                                Tidak ada data performa ruangan yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

<script>
    function setPerformancePeriod(val) {
        sessionStorage.setItem('performance_page_scroll', document.querySelector('main')?.scrollTop || 0);
        document.getElementById('performance_period_input').value = val;
        submitFormWithTurbo(document.getElementById('performanceFilterForm'));
    }

    function initRoomPerformanceScroll() {
        const scrollPos = sessionStorage.getItem('performance_page_scroll');
        if (scrollPos) {
            const mainEl = document.querySelector('main');
            if (mainEl) {
                mainEl.scrollTop = parseInt(scrollPos);
                setTimeout(() => {
                    mainEl.scrollTop = parseInt(scrollPos);
                }, 50);
                setTimeout(() => {
                    mainEl.scrollTop = parseInt(scrollPos);
                    sessionStorage.removeItem('performance_page_scroll');
                }, 150);
            }
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initRoomPerformanceScroll); } else { document.addEventListener('DOMContentLoaded', initRoomPerformanceScroll); }
</script>
@endsection
