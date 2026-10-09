@extends('layouts.app')

@section('title', 'Log Tinjauan Persetujuan - Admin SBA')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4 border-b border-[#E5E7EB] pb-6">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight tracking-tight text-[#0F172A]">
                Tinjauan SPV
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Log riwayat persetujuan dan penolakan SPV.
            </p>
        </div>
    </header>

    <!-- Filters Panel -->
    <section class="bg-white border border-[#E5E7EB] rounded-2xl p-6 shadow-sm mb-6">
        <form action="{{ route('admin.toilet_inspection.approvals.index') }}" method="GET" id="approvalsFilterForm" class="space-y-6" onsubmit="sessionStorage.setItem('approvals_page_scroll', document.querySelector('main')?.scrollTop || 0);">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Cari</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Catatan, petugas, SPV..." class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB]">
                        <span class="material-symbols-outlined text-slate-400 text-[18px] absolute left-3 top-2.5">search</span>
                    </div>
                </div>

                <!-- Location Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Lokasi</label>
                    <select name="location_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->building }} ({{ $loc->toilet_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Floor Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Lantai</label>
                    <select name="floor" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Lantai</option>
                        @foreach($locations->pluck('floor')->unique()->sort() as $floor)
                            <option value="{{ $floor }}" {{ request('floor') === $floor ? 'selected' : '' }}>
                                {{ $floor }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter (Decision) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Keputusan</label>
                    <select name="status" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Keputusan</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Ulasan</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 pt-2 border-t border-slate-100 items-end">
                <!-- Inspector Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Petugas Inspeksi</label>
                    <select name="inspector_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                        <option value="">Semua Petugas Inspeksi</option>
                        @foreach($inspectors as $inspector)
                            <option value="{{ $inspector->id }}" {{ request('inspector_id') == $inspector->id ? 'selected' : '' }}>
                                {{ $inspector->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Period Selector -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tipe Periode</label>
                    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5 shadow-sm text-xs font-semibold w-full justify-between">
                        <button type="button" onclick="setApprovalsPeriod('daily')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $dateFilter === 'daily' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Harian</button>
                        <button type="button" onclick="setApprovalsPeriod('weekly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $dateFilter === 'weekly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Mingguan</button>
                        <button type="button" onclick="setApprovalsPeriod('monthly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $dateFilter === 'monthly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Bulanan</button>
                        <button type="button" onclick="setApprovalsPeriod('yearly')" class="flex-1 py-1.5 rounded-md text-center transition-all {{ $dateFilter === 'yearly' ? 'bg-[#2563EB] text-white' : 'text-[#475569] hover:text-[#0F172A]' }}">Tahunan</button>
                    </div>
                    <input type="hidden" name="date_filter" id="approvals_period_input" value="{{ $dateFilter }}">
                </div>

                <!-- Custom Range Datepicker (start_date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Mulai Kustom</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" onchange="clearApprovalsPeriod()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                </div>

                <!-- Custom Range Datepicker (end_date) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tanggal Selesai Kustom</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" onchange="clearApprovalsPeriod()" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-[#2563EB] bg-white">
                </div>

                <!-- Action buttons -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-[#2563EB] text-white text-xs font-bold py-2 rounded-lg hover:bg-[#1D4ED8] transition-colors shadow-sm">
                        Terapkan
                    </button>
                    <a href="{{ route('admin.toilet_inspection.approvals.index') }}" onclick="sessionStorage.removeItem('approvals_page_scroll');" class="px-4 py-2 border border-gray-200 text-xs font-bold rounded-lg text-slate-700 hover:bg-slate-50 transition-colors shadow-sm text-center">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <!-- Table -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Keputusan</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">SPV</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Catatan / Feedback</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($inspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 font-medium">
                            {{ $ins->location->building }}, {{ $ins->location->floor }} ({{ $ins->location->toilet_name }} - {{ ucfirst($ins->location->gender) }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $badges = [
                                    'submitted' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                                    'approved' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                                    'rejected' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                                ];
                                $badgeClass = $badges[$ins->status] ?? 'bg-[#F8FAFC] text-[#64748B]';
                            @endphp
                            <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-medium {{ $badgeClass }} ring-1 ring-inset">
                                {{ $ins->status === 'submitted' ? 'Menunggu Ulasan' : ($ins->status === 'approved' ? 'Disetujui' : ($ins->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->approval ? $ins->approval->approver->name : '-' }}
                        </td>
                        <td class="px-6 py-4 text-[#475569] max-w-xs truncate" title="{{ $ins->approval ? $ins->approval->notes : '' }}">
                            {{ $ins->approval ? ($ins->approval->notes ?: '-') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->approval ? $ins->approval->actioned_at->format('d M Y, H:i') : $ins->inspected_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#64748B]">Tidak ada ulasan yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($inspections->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $inspections->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function setApprovalsPeriod(val) {
        sessionStorage.setItem('approvals_page_scroll', document.querySelector('main')?.scrollTop || 0);
        document.getElementById('approvals_period_input').value = val;
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
        submitFormWithTurbo(document.getElementById('approvalsFilterForm'));
    }

    function clearApprovalsPeriod() {
        document.getElementById('approvals_period_input').value = 'custom';
    }

    function initApprovalsScroll() {
        const scrollPos = sessionStorage.getItem('approvals_page_scroll');
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
                    sessionStorage.removeItem('approvals_page_scroll');
                }, 300);
            }
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initApprovalsScroll); } else { document.addEventListener('DOMContentLoaded', initApprovalsScroll); }
</script>
@endsection
