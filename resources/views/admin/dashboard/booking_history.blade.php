@extends('layouts.app')

@section('title', 'Riwayat Penggunaan Ruangan - SBA')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4 border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0F172A]">
                Riwayat Penggunaan Ruangan
            </h1>
            <p class="text-sm text-[#475569] mt-1">Log komprehensif dari semua reservasi ruangan, pelacakan status, dan metrik.</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg bg-white border border-[#E5E7EB] px-4 py-2 text-xs font-bold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Kembali ke Dashboard
            </a>
        </div>
    </header>

    <!-- Filters Panel -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <form action="{{ route('admin.booking_history') }}" method="GET" id="historyFilterForm" class="space-y-6" onsubmit="sessionStorage.setItem('booking_history_scroll', document.querySelector('main')?.scrollTop || 0);">
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cari</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Pengguna atau judul meeting..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <span class="material-symbols-outlined text-slate-400 text-[18px] absolute left-3 top-2.5">search</span>
                    </div>
                </div>

                <!-- Room Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ruangan</label>
                    <select name="room_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Ruangan</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pengguna</label>
                    <select name="user_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Period/Date Range Toggles -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Periode</label>
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold w-full justify-between">
                        <button type="button" onclick="setHistoryPeriod('daily')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                        <button type="button" onclick="setHistoryPeriod('weekly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                        <button type="button" onclick="setHistoryPeriod('monthly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                        <button type="button" onclick="setHistoryPeriod('yearly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                    </div>
                    <input type="hidden" name="period" id="history_period_input" value="{{ $period }}">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-2 border-t border-slate-100 items-end">
                <!-- Custom Period Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal / Bulan / Tahun Terpilih</label>
                    @if($period === 'yearly')
                        <select name="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                            @for($y = 2024; $y <= 2030; $y++)
                                <option value="{{ $y }}" {{ $dateInput == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($period === 'monthly')
                        <input type="month" name="date" value="{{ $dateInput }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    @elseif($period)
                        <input type="date" name="date" value="{{ $dateInput }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                    @else
                        <div class="text-xs text-slate-400 italic py-2">Dinonaktifkan (Rentang tanggal kustom aktif)</div>
                    @endif
                </div>

                <!-- Custom Range Datepicker (start_date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Mulai Kustom</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" onchange="clearPeriod()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                </div>

                <!-- Custom Range Datepicker (end_date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Selesai Kustom</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" onchange="clearPeriod()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                </div>

                <!-- Action buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-[#2563EB] text-white text-xs font-bold py-2 rounded-lg hover:bg-[#1D4ED8] transition-colors shadow-sm">
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.booking_history') }}" onclick="sessionStorage.removeItem('booking_history_scroll');" class="px-4 py-2 border border-gray-200 text-xs font-bold rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center">
                        Reset
                    </a>
                </div>
            </div>

        </form>
    </section>

    <!-- Table List -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E5E7EB] text-left">
                <thead>
                    <tr class="bg-[#F9FAFB] text-xs font-bold text-[#475569] uppercase tracking-wider">
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4">Ruangan</th>
                        <th class="px-6 py-4">Meeting / Judul</th>
                        <th class="px-6 py-4">Tanggal & Waktu</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB] text-xs text-[#0F172A]">
                    @forelse($bookings as $b)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $b->user->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->user->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-[#2563EB]">{{ $b->room ? $b->room->name : 'Online' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $b->room ? $b->room->location : 'Microsoft Teams' }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-[200px] truncate" title="{{ $b->title }}">
                                {{ $b->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $b->booking_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ substr($b->start_time, 0, 5) }} - {{ substr($b->end_time, 0, 5) }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold inline-block
                                    @if($b->status === 'approved' || $b->status === 'completed') bg-green-50 text-green-700 border border-green-200
                                    @elseif($b->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
                                    @else bg-red-50 text-red-700 border border-red-200
                                    @endif">
                                    {{ $b->status === 'pending' ? 'Menunggu' : ($b->status === 'approved' ? 'Disetujui' : ($b->status === 'rejected' ? 'Ditolak' : ($b->status === 'cancelled' ? 'Dibatalkan' : 'Selesai'))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('bookings.show', $b) }}" class="text-xs font-bold text-[#2563EB] hover:underline">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-20 text-slate-400 italic bg-white">
                                <span class="material-symbols-outlined text-[36px] text-slate-300 mb-2 block">book_online</span>
                                Tidak ada riwayat pemesanan yang ditemukan sesuai dengan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($bookings->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>

</div>

<script>
    function setHistoryPeriod(val) {
        sessionStorage.setItem('booking_history_scroll', document.querySelector('main')?.scrollTop || 0);
        document.getElementById('history_period_input').value = val;
        // Clear custom range when period button is pressed
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        submitFormWithTurbo(document.getElementById('historyFilterForm'));
    }

    function clearPeriod() {
        document.getElementById('history_period_input').value = '';
    }

    function initBookingHistoryScroll() {
        // Restore scroll position
        const scrollPos = sessionStorage.getItem('booking_history_scroll');
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
                    sessionStorage.removeItem('booking_history_scroll');
                }, 300);
            }
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initBookingHistoryScroll); } else { document.addEventListener('DOMContentLoaded', initBookingHistoryScroll); }
</script>
@endsection
