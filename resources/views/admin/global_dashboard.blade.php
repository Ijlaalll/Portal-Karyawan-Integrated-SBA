@extends('layouts.app')

@section('title', 'Dashboard Admin Global - SBA')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:py-8 px-4 sm:px-6 lg:px-8 space-y-8 sm:space-y-12">
    
    <!-- Dashboard Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F172A] tracking-tight">
                Dashboard Admin Global
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Ringkasan performa dan penggunaan modul.
            </p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 1. MEETING ROOM OVERVIEW                   -->
    <!-- ========================================== -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm space-y-6">
        <!-- Section Header & Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#F3F4F6] pb-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">meeting_room</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Ringkasan Ruang Meeting</h2>
                    <p class="text-xs text-[#64748B]">Pola penggunaan dan pemesanan ruangan</p>
                </div>
            </div>
            
            <!-- Filters Form -->
            <form action="{{ route('admin.home') }}" method="GET" id="mrFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3" onsubmit="sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <!-- Keep other filters state -->
                <input type="hidden" name="ti_period" value="{{ $toiletInspectionOverview['period'] }}">
                <input type="hidden" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}">
                <input type="hidden" name="atk_period" value="{{ $atkOverview['period'] }}">
                <input type="hidden" name="atk_date" value="{{ $atkOverview['date_input'] }}">
                <input type="hidden" name="sik_period" value="{{ $sikOverview['period'] }}">
                <input type="hidden" name="sik_date" value="{{ $sikOverview['date_input'] }}">
                <input type="hidden" name="vhc_period" value="{{ $vehicleOverview['period'] }}">
                <input type="hidden" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}">

                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
                    @if($meetingRoomOverview['period'] === 'yearly')
                        {{ $meetingRoomOverview['start_date']->format('Y') }}
                    @elseif($meetingRoomOverview['period'] === 'monthly')
                        {{ $meetingRoomOverview['start_date']->format('F Y') }}
                    @elseif($meetingRoomOverview['period'] === 'weekly')
                        {{ $meetingRoomOverview['start_date']->format('d M') }} - {{ $meetingRoomOverview['end_date']->format('d M Y') }}
                    @else
                        {{ $meetingRoomOverview['start_date']->format('d M Y') }}
                    @endif
                </span>

                <div class="w-full sm:w-auto grid grid-cols-4 sm:inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold text-center">
                    <button type="button" onclick="setMrPeriod('daily')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $meetingRoomOverview['period'] === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setMrPeriod('weekly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $meetingRoomOverview['period'] === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setMrPeriod('monthly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $meetingRoomOverview['period'] === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setMrPeriod('yearly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $meetingRoomOverview['period'] === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="mr_period" id="mr_period_input" value="{{ $meetingRoomOverview['period'] }}">

                <div class="w-full sm:w-auto">
                    @if($meetingRoomOverview['period'] === 'yearly')
                        <select name="mr_date" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $meetingRoomOverview['date_input'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($meetingRoomOverview['period'] === 'monthly')
                        <input type="month" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">book_online</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Pemesanan</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $meetingRoomOverview['total_bookings'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">star</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Ruangan Paling Aktif</p>
                    <h3 class="text-sm font-extrabold text-[#0F172A] mt-0.5 truncate" title="{{ $meetingRoomOverview['most_used_room'] }}">
                        {{ $meetingRoomOverview['most_used_room'] }}
                    </h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#F0FDF4] text-[#16A34A] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">meeting_room</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Ruangan Beroperasi</p>
                    <h3 class="text-base font-extrabold text-[#0F172A] mt-0.5 truncate">
                        {{ $meetingRoomOverview['active_rooms'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $meetingRoomOverview['total_rooms'] }} aktif</span>
                    </h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px] fill-amber-500 text-amber-500" style="font-variation-settings: 'FILL' 1;">grade</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Rating Rata-rata</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">
                        ★ {{ $meetingRoomOverview['average_rating'] > 0 ? number_format($meetingRoomOverview['average_rating'], 1) : 'N/A' }}
                    </h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">rate_review</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Ulasan</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $meetingRoomOverview['total_reviews'] }}</h3>
                </div>
            </div>
        </div>



        <!-- Action Button -->
        <div class="flex justify-end pt-4 border-t border-[#F3F4F6]">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                Buka Dashboard Ruang Meeting
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 2. TOILET INSPECTION OVERVIEW              -->
    <!-- ========================================== -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm space-y-6">
        <!-- Section Header & Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#F3F4F6] pb-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-[#FEF3C7] text-[#D97706] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">wc</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Ringkasan Inspeksi Toilet</h2>
                    <p class="text-xs text-[#64748B]">Hasil inspeksi, tindakan SPV, dan skor kualitas</p>
                </div>
            </div>
            
            <!-- Filters Form -->
            <form action="{{ route('admin.home') }}" method="GET" id="tiFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3" onsubmit="sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <!-- Keep other filters state -->
                <input type="hidden" name="mr_period" value="{{ $meetingRoomOverview['period'] }}">
                <input type="hidden" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}">
                <input type="hidden" name="atk_period" value="{{ $atkOverview['period'] }}">
                <input type="hidden" name="atk_date" value="{{ $atkOverview['date_input'] }}">
                <input type="hidden" name="sik_period" value="{{ $sikOverview['period'] }}">
                <input type="hidden" name="sik_date" value="{{ $sikOverview['date_input'] }}">
                <input type="hidden" name="vhc_period" value="{{ $vehicleOverview['period'] }}">
                <input type="hidden" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}">

                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
                    @if($toiletInspectionOverview['period'] === 'yearly')
                        {{ $toiletInspectionOverview['start_date']->format('Y') }}
                    @elseif($toiletInspectionOverview['period'] === 'monthly')
                        {{ $toiletInspectionOverview['start_date']->format('F Y') }}
                    @elseif($toiletInspectionOverview['period'] === 'weekly')
                        {{ $toiletInspectionOverview['start_date']->format('d M') }} - {{ $toiletInspectionOverview['end_date']->format('d M Y') }}
                    @else
                        {{ $toiletInspectionOverview['start_date']->format('d M Y') }}
                    @endif
                </span>

                <div class="w-full sm:w-auto grid grid-cols-4 sm:inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold text-center">
                    <button type="button" onclick="setTiPeriod('daily')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $toiletInspectionOverview['period'] === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setTiPeriod('weekly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $toiletInspectionOverview['period'] === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setTiPeriod('monthly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $toiletInspectionOverview['period'] === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setTiPeriod('yearly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $toiletInspectionOverview['period'] === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="ti_period" id="ti_period_input" value="{{ $toiletInspectionOverview['period'] }}">

                <div class="w-full sm:w-auto">
                    @if($toiletInspectionOverview['period'] === 'yearly')
                        <select name="ti_date" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $toiletInspectionOverview['date_input'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($toiletInspectionOverview['period'] === 'monthly')
                        <input type="month" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FEF3C7] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">analytics</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Inspeksi</p>
                    <h3 class="text-xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $toiletInspectionOverview['total_inspections'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#F0FDF4] text-[#16A34A] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Ulasan Selesai</p>
                    <h3 class="text-xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $toiletInspectionOverview['completed'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Tindakan Menunggu</p>
                    <h3 class="text-xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $toiletInspectionOverview['pending'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFF1F2] text-[#E11D48] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Ditolak</p>
                    <h3 class="text-xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $toiletInspectionOverview['rejected'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FAF5FF] text-[#9333EA] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">percent</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Rasio Persetujuan</p>
                    <h3 class="text-xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $toiletInspectionOverview['approval_rate'] }}%</h3>
                </div>
            </div>
        </div>



        <!-- Action Button -->
        <div class="flex justify-end pt-4 border-t border-[#F3F4F6]">
            <a href="{{ route('admin.toilet_inspection.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                Buka Dashboard Inspeksi Toilet
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 3. MODULE 3 OVERVIEW (ATK MANAGEMENT)      -->
    <!-- ========================================== -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#F3F4F6] pb-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-green-50 text-green-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">inventory_2</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Ringkasan Alat Tulis Kantor</h2>
                    <p class="text-xs text-[#64748B]">Manajemen Alat Tulis Kantor</p>
                </div>
            </div>

            <!-- Filters Form -->
            <form action="{{ route('admin.home') }}" method="GET" id="atkFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3" onsubmit="sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <!-- Keep other filters state -->
                <input type="hidden" name="mr_period" value="{{ $meetingRoomOverview['period'] }}">
                <input type="hidden" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}">
                <input type="hidden" name="ti_period" value="{{ $toiletInspectionOverview['period'] }}">
                <input type="hidden" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}">
                <input type="hidden" name="sik_period" value="{{ $sikOverview['period'] }}">
                <input type="hidden" name="sik_date" value="{{ $sikOverview['date_input'] }}">
                <input type="hidden" name="vhc_period" value="{{ $vehicleOverview['period'] }}">
                <input type="hidden" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}">

                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
                    @if($atkOverview['period'] === 'yearly')
                        {{ $atkOverview['start_date']->format('Y') }}
                    @elseif($atkOverview['period'] === 'monthly')
                        {{ $atkOverview['start_date']->format('F Y') }}
                    @elseif($atkOverview['period'] === 'weekly')
                        {{ $atkOverview['start_date']->format('d M') }} - {{ $atkOverview['end_date']->format('d M Y') }}
                    @else
                        {{ $atkOverview['start_date']->format('d M Y') }}
                    @endif
                </span>

                <div class="w-full sm:w-auto grid grid-cols-4 sm:inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold text-center">
                    <button type="button" onclick="setAtkPeriod('daily')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $atkOverview['period'] === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setAtkPeriod('weekly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $atkOverview['period'] === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setAtkPeriod('monthly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $atkOverview['period'] === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setAtkPeriod('yearly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $atkOverview['period'] === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="atk_period" id="atk_period_input" value="{{ $atkOverview['period'] }}">

                <div class="w-full sm:w-auto">
                    @if($atkOverview['period'] === 'yearly')
                        <select name="atk_date" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $atkOverview['date_input'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($atkOverview['period'] === 'monthly')
                        <input type="month" name="atk_date" value="{{ $atkOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="atk_date" value="{{ $atkOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">shopping_bag</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Barang</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $atkOverview['total_items'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#F5F3FF] text-[#7C3AED] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">style</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Varian</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $atkOverview['total_variants'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Request Menunggu</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $atkOverview['pending_requests'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">warning</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Stok Menipis</p>
                    <h3 class="text-xl font-extrabold text-amber-600 mt-0.5 truncate">{{ $atkOverview['low_stock'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFF1F2] text-[#E11D48] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">production_quantity_limits</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Stok Habis</p>
                    <h3 class="text-xl font-extrabold text-red-600 mt-0.5 truncate">{{ $atkOverview['out_of_stock'] }}</h3>
                </div>
            </div>
        </div>

        <!-- Recent Requests Activity Table -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-[#0F172A]">Permintaan Terbaru</h3>
            <div class="overflow-x-auto border border-[#E5E7EB] rounded-xl">
                <table class="w-full text-left text-sm text-[#475569]">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-[10px] font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                            <th class="py-2.5 px-4">No. Request</th>
                            <th class="py-2.5 px-4">Pemohon</th>
                            <th class="py-2.5 px-4">Tanggal Pengajuan</th>
                            <th class="py-2.5 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($atkOverview['recent_requests'] as $req)
                            <tr>
                                <td class="py-2 px-4 font-semibold text-[#2563EB] font-mono text-xs">{{ $req->request_number }}</td>
                                <td class="py-2 px-4 text-xs">{{ $req->user->name }}</td>
                                <td class="py-2 px-4 text-xs">{{ $req->created_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                                <td class="py-2 px-4 text-xs">
                                    @php
                                        $badges = [
                                            'diajukan' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
                                            'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                            'diproses' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                            'selesai' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                            'ditolak' => 'bg-red-50 text-red-700 ring-red-600/20',
                                            'dibatalkan' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        ];
                                        $badgeClass = $badges[$req->status] ?? 'bg-slate-50 text-slate-600';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $badgeClass }} ring-1 ring-inset">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center italic text-xs text-slate-400">Belum ada aktivitas pengajuan ATK.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-[#F3F4F6]">
            <a href="{{ route('atk.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                Lihat Manajemen ATK
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 4. SURAT IZIN KELUAR (SIK) OVERVIEW        -->
    <!-- ========================================== -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm space-y-6">
        <!-- Section Header & Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#F3F4F6] pb-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">assignment_ind</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Ringkasan Surat Izin Keluar (SIK)</h2>
                    <p class="text-xs text-[#64748B]">Penggunaan izin keluar karyawan, status persetujuan, dan kontrol gerbang pabrik</p>
                </div>
            </div>

            <!-- Filters Form -->
            <form action="{{ route('admin.home') }}" method="GET" id="sikFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3" onsubmit="sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <!-- Keep other filters state -->
                <input type="hidden" name="mr_period" value="{{ $meetingRoomOverview['period'] }}">
                <input type="hidden" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}">
                <input type="hidden" name="ti_period" value="{{ $toiletInspectionOverview['period'] }}">
                <input type="hidden" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}">
                <input type="hidden" name="atk_period" value="{{ $atkOverview['period'] }}">
                <input type="hidden" name="atk_date" value="{{ $atkOverview['date_input'] }}">
                <input type="hidden" name="vhc_period" value="{{ $vehicleOverview['period'] }}">
                <input type="hidden" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}">

                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
                    @if($sikOverview['period'] === 'yearly')
                        {{ $sikOverview['start_date']->format('Y') }}
                    @elseif($sikOverview['period'] === 'monthly')
                        {{ $sikOverview['start_date']->format('F Y') }}
                    @elseif($sikOverview['period'] === 'weekly')
                        {{ $sikOverview['start_date']->format('d M') }} - {{ $sikOverview['end_date']->format('d M Y') }}
                    @else
                        {{ $sikOverview['start_date']->format('d M Y') }}
                    @endif
                </span>

                <div class="w-full sm:w-auto grid grid-cols-4 sm:inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold text-center">
                    <button type="button" onclick="setSikPeriod('daily')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $sikOverview['period'] === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setSikPeriod('weekly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $sikOverview['period'] === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setSikPeriod('monthly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $sikOverview['period'] === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setSikPeriod('yearly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $sikOverview['period'] === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="sik_period" id="sik_period_input" value="{{ $sikOverview['period'] }}">

                <div class="w-full sm:w-auto">
                    @if($sikOverview['period'] === 'yearly')
                        <select name="sik_date" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $sikOverview['date_input'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($sikOverview['period'] === 'monthly')
                        <input type="month" name="sik_date" value="{{ $sikOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="sik_date" value="{{ $sikOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Total SIK</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $sikOverview['total_permits'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Menunggu Persetujuan</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $sikOverview['pending_approval'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#F5F3FF] text-[#7C3AED] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">directions_walk</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Sedang Di Luar</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $sikOverview['active_outside'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#FFF1F2] text-[#E11D48] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">timer_off</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Terlambat Kembali</p>
                    <h3 class="text-xl font-extrabold text-rose-600 mt-0.5 truncate">{{ $sikOverview['overdue'] }}</h3>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-[#F0FDF4] text-[#16A34A] flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Kembali Hari Ini</p>
                    <h3 class="text-xl font-extrabold text-emerald-600 mt-0.5 truncate">{{ $sikOverview['returned_today'] }}</h3>
                </div>
            </div>
        </div>

        <!-- Recent SIK Permits Activity Table -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-[#0F172A]">Pengajuan SIK Terbaru</h3>
            <div class="overflow-x-auto border border-[#E5E7EB] rounded-xl">
                <table class="w-full text-left text-sm text-[#475569]">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-[10px] font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                            <th class="py-2.5 px-4">No. SIK</th>
                            <th class="py-2.5 px-4">Pemohon</th>
                            <th class="py-2.5 px-4">Departemen</th>
                            <th class="py-2.5 px-4">Keperluan</th>
                            <th class="py-2.5 px-4">Tanggal & Waktu</th>
                            <th class="py-2.5 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($sikOverview['recent_requests'] as $sik)
                            <tr>
                                <td class="py-2 px-4 font-semibold text-[#2563EB] font-mono text-xs">
                                    <a href="{{ route('sik.show', $sik) }}" class="hover:underline">{{ $sik->sik_number }}</a>
                                </td>
                                <td class="py-2 px-4 text-xs font-medium text-slate-800">{{ $sik->user->name ?? '-' }}</td>
                                <td class="py-2 px-4 text-xs text-slate-600">{{ $sik->department }}</td>
                                <td class="py-2 px-4 text-xs max-w-[200px] truncate" title="{{ $sik->purpose }}">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $sik->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }} mr-1">
                                        {{ $sik->purpose_type }}
                                    </span>
                                    {{ $sik->purpose }}
                                </td>
                                <td class="py-2 px-4 text-xs whitespace-nowrap">
                                    <span>{{ $sik->permit_date->format('d M Y') }}</span>
                                    <span class="text-slate-400 font-mono text-[10px] ml-1">({{ $sik->expected_departure_time }} - {{ $sik->expected_return_time }})</span>
                                </td>
                                <td class="py-2 px-4 text-xs whitespace-nowrap">
                                    @php $effStatus = $sik->effective_status; @endphp
                                    @if($effStatus === 'draft')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">DRAFT</span>
                                    @elseif($effStatus === 'pending_approval')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">MENUNGGU</span>
                                    @elseif($effStatus === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">DISETUJUI</span>
                                    @elseif($effStatus === 'outside')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800">DI LUAR</span>
                                    @elseif($effStatus === 'overdue')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-200 animate-pulse">TERLAMBAT</span>
                                    @elseif($effStatus === 'returned')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">KEMBALI</span>
                                    @elseif($effStatus === 'closed_manually')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-300">TUTUP MANUAL</span>
                                    @elseif($effStatus === 'revoked')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">DICABUT</span>
                                    @elseif($effStatus === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-800 border border-red-200">DITOLAK</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center italic text-xs text-slate-400">Belum ada pengajuan Surat Izin Keluar (SIK).</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-[#F3F4F6]">
            <a href="{{ route('admin.sik.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                Lihat Dashboard Administrasi SIK
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- ========================================== -->
    <!-- 5. VEHICLE USAGE OVERVIEW                  -->
    <!-- ========================================== -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm space-y-6">
        <!-- Section Header & Filter -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 border-b border-[#F3F4F6] pb-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-lg bg-blue-50 text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">directions_car</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-[#0F172A]">Ringkasan Kendaraan</h2>
                    <p class="text-xs text-[#64748B]">Monitoring permintaan operasional, ketersediaan unit, dan status penugasan</p>
                </div>
            </div>
            
            <!-- Filters Form -->
            <form action="{{ route('admin.home') }}" method="GET" id="vhcFilterForm" class="flex flex-wrap items-center gap-2.5 sm:gap-3" onsubmit="sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);">
                <!-- Keep other filters state -->
                <input type="hidden" name="mr_period" value="{{ $meetingRoomOverview['period'] }}">
                <input type="hidden" name="mr_date" value="{{ $meetingRoomOverview['date_input'] }}">
                <input type="hidden" name="ti_period" value="{{ $toiletInspectionOverview['period'] }}">
                <input type="hidden" name="ti_date" value="{{ $toiletInspectionOverview['date_input'] }}">
                <input type="hidden" name="atk_period" value="{{ $atkOverview['period'] }}">
                <input type="hidden" name="atk_date" value="{{ $atkOverview['date_input'] }}">
                <input type="hidden" name="sik_period" value="{{ $sikOverview['period'] }}">
                <input type="hidden" name="sik_date" value="{{ $sikOverview['date_input'] }}">

                <!-- Date Range Label Badge -->
                <span class="text-xs font-bold text-[#475569] bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg whitespace-nowrap">
                    @if($vehicleOverview['period'] === 'yearly')
                        {{ $vehicleOverview['start_date']->format('Y') }}
                    @elseif($vehicleOverview['period'] === 'monthly')
                        {{ $vehicleOverview['start_date']->format('F Y') }}
                    @elseif($vehicleOverview['period'] === 'weekly')
                        {{ $vehicleOverview['start_date']->format('d M') }} - {{ $vehicleOverview['end_date']->format('d M Y') }}
                    @else
                        {{ $vehicleOverview['start_date']->format('d M Y') }}
                    @endif
                </span>

                <div class="w-full sm:w-auto grid grid-cols-4 sm:inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold text-center">
                    <button type="button" onclick="setVhcPeriod('daily')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $vehicleOverview['period'] === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                    <button type="button" onclick="setVhcPeriod('weekly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $vehicleOverview['period'] === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                    <button type="button" onclick="setVhcPeriod('monthly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $vehicleOverview['period'] === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                    <button type="button" onclick="setVhcPeriod('yearly')" class="px-1.5 sm:px-3 py-1.5 rounded-md transition-all text-center {{ $vehicleOverview['period'] === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                </div>
                <input type="hidden" name="vhc_period" id="vhc_period_input" value="{{ $vehicleOverview['period'] }}">

                <div class="w-full sm:w-auto">
                    @if($vehicleOverview['period'] === 'yearly')
                        <select name="vhc_date" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $vehicleOverview['date_input'] == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($vehicleOverview['period'] === 'monthly')
                        <input type="month" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @else
                        <input type="date" name="vhc_date" value="{{ $vehicleOverview['date_input'] }}" onchange="submitFormWithTurbo(this)" class="w-full sm:w-auto border border-gray-200 rounded-lg px-3 py-1.5 text-xs font-medium text-[#475569] bg-white focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            <!-- Permohonan Periode Ini -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">assignment</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Permohonan (Periode)</p>
                    <h3 class="text-xl font-extrabold text-[#0F172A] mt-0.5 truncate">{{ $vehicleOverview['total_requests'] }}</h3>
                </div>
            </div>

            <!-- Sedang Digunakan Saat Ini (Real-Time) -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">near_me</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Sedang Digunakan (Saat Ini)</p>
                    <h3 class="text-xl font-extrabold text-indigo-700 mt-0.5 truncate">{{ $vehicleOverview['in_use_now'] }}</h3>
                </div>
            </div>

            <!-- Menunggu Persetujuan (Periode) -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">pending_actions</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Menunggu Persetujuan</p>
                    <h3 class="text-xl font-extrabold text-amber-600 mt-0.5 truncate">{{ $vehicleOverview['pending_count'] }}</h3>
                </div>
            </div>

            <!-- Unit Tersedia Saat Ini -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Unit Tersedia (Ready)</p>
                    <h3 class="text-xl font-extrabold text-emerald-600 mt-0.5 truncate">{{ $vehicleOverview['available_units'] }} <span class="text-xs font-normal text-slate-400">/ {{ $vehicleOverview['total_units'] }}</span></h3>
                </div>
            </div>

            <!-- Tingkat Persetujuan -->
            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex items-center gap-4 h-[76px]">
                <div class="h-10 w-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">percent</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-semibold text-[#64748B] uppercase tracking-wider truncate">Rasio Disetujui</p>
                    <h3 class="text-xl font-extrabold text-purple-700 mt-0.5 truncate">{{ $vehicleOverview['approval_rate'] }}%</h3>
                </div>
            </div>
        </div>

        <!-- Recent Vehicle Requests Activity Table -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-[#0F172A]">Permohonan Kendaraan (Periode Ini)</h3>
            <div class="overflow-x-auto border border-[#E5E7EB] rounded-xl">
                <table class="w-full text-left text-sm text-[#475569]">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-[10px] font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                            <th class="py-2.5 px-4">No. Permohonan</th>
                            <th class="py-2.5 px-4">Pemohon</th>
                            <th class="py-2.5 px-4">Tujuan</th>
                            <th class="py-2.5 px-4">Jadwal Penggunaan</th>
                            <th class="py-2.5 px-4">Kendaraan / Supir</th>
                            <th class="py-2.5 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($vehicleOverview['recent_requests'] as $req)
                            <tr>
                                <td class="py-2 px-4 font-semibold text-[#2563EB] font-mono text-xs">
                                    <a href="{{ route('vehicles.requests.show', $req) }}" class="hover:underline">{{ $req->request_number }}</a>
                                </td>
                                <td class="py-2 px-4 text-xs font-medium text-slate-800">{{ $req->user->name ?? '-' }}</td>
                                <td class="py-2 px-4 text-xs max-w-[200px] truncate" title="{{ $req->destination }}">
                                    <p class="font-medium text-slate-800 truncate">{{ $req->destination }}</p>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $req->purpose }}</p>
                                </td>
                                <td class="py-2 px-4 text-xs whitespace-nowrap">
                                    <span>{{ $req->start_datetime->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</span>
                                    <span class="text-slate-400 font-mono text-[10px] ml-1">s/d {{ $req->end_datetime->setTimezone('Asia/Jakarta')->format('H:i') }}</span>
                                </td>
                                <td class="py-2 px-4 text-xs">
                                    @if($req->vehicle)
                                        <div class="font-medium text-slate-800">{{ $req->vehicle->name }} ({{ $req->vehicle->plate_number }})</div>
                                        <div class="text-[10px] text-slate-500">{{ $req->driver->name ?? 'Tanpa Supir' }}</div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-xs whitespace-nowrap">
                                    @if($req->status === 'pending_approval')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800">MENUNGGU</span>
                                    @elseif($req->status === 'approved')
                                        @php
                                            $nowJkt = \Carbon\Carbon::now('Asia/Jakarta');
                                            $isTripInUse = ($req->start_datetime <= $nowJkt && $req->end_datetime > $nowJkt);
                                        @endphp
                                        @if($isTripInUse)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200 animate-pulse">BERJALAN</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">DISETUJUI</span>
                                        @endif
                                    @elseif($req->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">SELESAI</span>
                                    @elseif($req->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">DITOLAK</span>
                                    @elseif($req->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">DIBATALKAN</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">{{ strtoupper($req->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center italic text-xs text-slate-400">Belum ada permohonan kendaraan pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-[#F3F4F6]">
            <a href="{{ route('vehicles.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] transition-colors">
                Buka Dashboard Kendaraan
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </section>

</div>

<!-- scripts integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Universal Global Dashboard period switcher maintaining independent section states
    function setPeriodUrl(targetSection, val) {
        sessionStorage.setItem('global_dashboard_scroll', document.querySelector('main')?.scrollTop || 0);
        const now = new Date();
        let targetDate = '';
        if (val === 'yearly') {
            targetDate = now.getFullYear();
        } else if (val === 'monthly') {
            targetDate = now.toISOString().slice(0, 7);
        } else {
            targetDate = now.toISOString().slice(0, 10);
        }

        const params = new URLSearchParams({
            mr_period: targetSection === 'mr' ? val : '{{ $meetingRoomOverview['period'] }}',
            mr_date: targetSection === 'mr' ? targetDate : '{{ $meetingRoomOverview['date_input'] }}',
            ti_period: targetSection === 'ti' ? val : '{{ $toiletInspectionOverview['period'] }}',
            ti_date: targetSection === 'ti' ? targetDate : '{{ $toiletInspectionOverview['date_input'] }}',
            atk_period: targetSection === 'atk' ? val : '{{ $atkOverview['period'] }}',
            atk_date: targetSection === 'atk' ? targetDate : '{{ $atkOverview['date_input'] }}',
            sik_period: targetSection === 'sik' ? val : '{{ $sikOverview['period'] }}',
            sik_date: targetSection === 'sik' ? targetDate : '{{ $sikOverview['date_input'] }}',
            vhc_period: targetSection === 'vhc' ? val : '{{ $vehicleOverview['period'] }}',
            vhc_date: targetSection === 'vhc' ? targetDate : '{{ $vehicleOverview['date_input'] }}',
        });

        const targetUrl = `{{ route('admin.home') }}?` + params.toString();
        if (window.navigateTo) { window.navigateTo(targetUrl); } else { window.location.href = targetUrl; }
    }

    function setMrPeriod(val) { setPeriodUrl('mr', val); }
    function setTiPeriod(val) { setPeriodUrl('ti', val); }
    function setAtkPeriod(val) { setPeriodUrl('atk', val); }
    function setSikPeriod(val) { setPeriodUrl('sik', val); }
    function setVhcPeriod(val) { setPeriodUrl('vhc', val); }

    window.setPeriodUrl = setPeriodUrl;
    window.setMrPeriod = setMrPeriod;
    window.setTiPeriod = setTiPeriod;
    window.setAtkPeriod = setAtkPeriod;
    window.setSikPeriod = setSikPeriod;
    window.setVhcPeriod = setVhcPeriod;

    // Initial Chart.js charts setup
    function initGlobalDashboard() {
        const scrollPos = sessionStorage.getItem('global_dashboard_scroll');
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
                    sessionStorage.removeItem('global_dashboard_scroll');
                }, 300);
            }
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initGlobalDashboard); } else { document.addEventListener('DOMContentLoaded', initGlobalDashboard); }
</script>
@endsection
