@extends('layouts.app')

@section('title', 'Ulasan Ruangan - SBA')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4 border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0F172A]">
                Semua Ulasan Ruangan
            </h1>
            <p class="text-sm text-[#475569] mt-1">Log feedback, skor kualitas layanan, dan ulasan pengguna tertulis.</p>
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
        <form action="{{ route('admin.reviews') }}" method="GET" id="reviewsFilterForm" class="space-y-6" onsubmit="sessionStorage.setItem('reviews_page_scroll', document.querySelector('main')?.scrollTop || 0);">
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cari Komentar</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Pengguna atau teks komentar..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
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

                <!-- Rating Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Rating</label>
                    <select name="rating" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Rating</option>
                        <option value="5" {{ request('rating') == 5 ? 'selected' : '' }}>★ 5 Bintang</option>
                        <option value="4" {{ request('rating') == 4 ? 'selected' : '' }}>★ 4 Bintang</option>
                        <option value="3" {{ request('rating') == 3 ? 'selected' : '' }}>★ 3 Bintang</option>
                        <option value="2" {{ request('rating') == 2 ? 'selected' : '' }}>★ 2 Bintang</option>
                        <option value="1" {{ request('rating') == 1 ? 'selected' : '' }}>★ 1 Bintang</option>
                    </select>
                </div>

                <!-- Period Type Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Periode</label>
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold w-full justify-between">
                        <button type="button" onclick="setReviewsPeriod('daily')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                        <button type="button" onclick="setReviewsPeriod('weekly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                        <button type="button" onclick="setReviewsPeriod('monthly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                        <button type="button" onclick="setReviewsPeriod('yearly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $period === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                    </div>
                    <input type="hidden" name="period" id="reviews_period_input" value="{{ $period }}">
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
                    <a href="{{ route('admin.reviews') }}" class="px-4 py-2 border border-gray-200 text-xs font-bold rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center">
                        Reset
                    </a>
                </div>
            </div>

        </form>
    </section>

    <!-- Overall Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm">
        <!-- Average Rating -->
        <div class="flex flex-col justify-center items-center text-center p-4 border-b md:border-b-0 md:border-r border-slate-100">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Rating Rata-Rata</span>
            <div class="text-5xl font-extrabold text-[#0f172a] mt-2">{{ number_format($averageRating, 1) }}</div>
            <div class="flex items-center gap-0.5 mt-2">
                @for($i = 1; $i <= 5; $i++)
                    <span class="material-symbols-outlined text-[20px] {{ $i <= round($averageRating) ? 'text-amber-500' : 'text-slate-300' }}" {!! $i <= round($averageRating) ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>star</span>
                @endfor
            </div>
            <span class="text-xs text-slate-400 mt-2">Dari 5 bintang</span>
        </div>
 
        <!-- Total Reviews -->
        <div class="flex flex-col justify-center items-center text-center p-4 border-b md:border-b-0 md:border-r border-slate-100">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Ulasan</span>
            <div class="text-5xl font-extrabold text-[#2563EB] mt-2">{{ $totalReviews }}</div>
            <span class="text-xs text-slate-400 mt-4">Pemesanan selesai terverifikasi</span>
        </div>

        <!-- Rating Distribution -->
        <div class="flex flex-col justify-center p-4 space-y-2">
            @for($stars = 5; $stars >= 1; $stars--)
                @php
                    $count = $ratingDistribution[$stars] ?? 0;
                    $percent = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                @endphp
                <div class="flex items-center text-xs text-slate-600 font-semibold gap-3">
                    <span class="w-12 text-right shrink-0">{{ $stars }} Bintang</span>
                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-[#2563EB]" style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="w-8 text-slate-400 font-medium shrink-0 text-right">{{ $count }}</span>
                </div>
            @endfor
        </div>
    </div>

    <!-- Reviews Grid -->
    <section class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($reviews as $review)
                <div class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all">
                    
                    <div class="space-y-2">
                        <!-- Stars -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="material-symbols-outlined text-[18px] {{ $i <= $review->rating ? 'text-amber-500' : 'text-slate-300' }}" {!! $i <= $review->rating ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>star</span>
                                @endfor
                                <span class="text-xs font-bold text-[#0F172A] ml-1.5">{{ number_format($review->rating, 1) }} Bintang</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-bold bg-slate-100 px-2 py-0.5 rounded">{{ $review->booking_date->format('d M Y') }}</span>
                        </div>
 
                        <!-- Feedback Quote -->
                        <blockquote class="text-xs text-slate-700 italic border-l-2 border-[#2563EB] pl-3 py-1 bg-slate-50/30 rounded-r-md">
                            "{{ $review->feedback ?: 'Tidak ada feedback tertulis yang diberikan.' }}"
                        </blockquote>
                    </div>

                    <!-- Metadata details -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100/60 text-xs text-slate-500 font-semibold mt-auto shrink-0">
                        <div>
                            <div class="text-[#2563EB] font-extrabold">{{ $review->room ? $review->room->name : 'Online' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $review->room ? $review->room->location : 'Microsoft Teams' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-slate-800">oleh {{ $review->user->name }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">Ref: Pemesanan #{{ $review->id }}</div>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-2 text-center py-20 text-slate-400 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm italic">
                    <span class="material-symbols-outlined text-[48px] text-slate-300 mb-2 block">rate_review</span>
                    Tidak ada ulasan ruangan yang ditemukan sesuai dengan filter yang dipilih.
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        @if($reviews->hasPages())
            <div class="px-6 py-4 bg-white border border-[#E5E7EB] rounded-2xl shadow-sm">
                {{ $reviews->links() }}
            </div>
        @endif
    </section>

</div>

<script>
    function setReviewsPeriod(val) {
        sessionStorage.setItem('reviews_page_scroll', document.querySelector('main')?.scrollTop || 0);
        document.getElementById('reviews_period_input').value = val;
        // Clear custom range when period button is pressed
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        submitFormWithTurbo(document.getElementById('reviewsFilterForm'));
    }

    function clearPeriod() {
        document.getElementById('reviews_period_input').value = '';
    }

    function initReviewsScroll() {
        // Restore scroll position
        const scrollPos = sessionStorage.getItem('reviews_page_scroll');
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
                    sessionStorage.removeItem('reviews_page_scroll');
                }, 300);
            }
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initReviewsScroll); } else { document.addEventListener('DOMContentLoaded', initReviewsScroll); }
</script>
@endsection
