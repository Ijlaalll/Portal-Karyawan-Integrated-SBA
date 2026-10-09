@extends('layouts.app')

@section('title', 'Laporan Inspeksi Toilet - SBA')

@section('content')
<style>
    @media print {
        /* Hide everything not related to the print report */
        nav, 
        header, 
        #filter-form-container, 
        .no-print, 
        .pagination-container {
            display: none !important;
        }
        
        /* Expand content to full screen */
        body {
            background-color: white !important;
            color: black !important;
            font-size: 12px !important;
        }
        
        .flex-1, main {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        
        /* Table styles for printing */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px !important;
        }
        
        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            text-align: left !important;
        }
        
        th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        
        /* Stats cards print styles */
        .stats-grid-print {
            display: grid !important;
            grid-template-cols: repeat(4, 1fr) !important;
            gap: 15px !important;
            margin-bottom: 20px !important;
        }
        
        .stat-card-print {
            border: 1px solid #cbd5e1 !important;
            padding: 10px !important;
            border-radius: 6px !important;
            background-color: #f8fafc !important;
        }
    }
</style>

<div class="min-h-full">
    <!-- Non-Print Header -->
    <header class="mb-8 flex justify-between items-center no-print">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Pelaporan Inspeksi Toilet
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Buat dan cetak laporan terstruktur kegiatan inspeksi toilet.
            </p>
        </div>
        <div>
            <button onclick="window.print();" class="rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak Laporan
            </button>
        </div>
    </header>

    <!-- Print-only Title Header -->
    <div class="hidden print:block mb-6 border-b border-[#cbd5e1] pb-4">
        <h1 class="text-2xl font-bold text-black">Laporan Inspeksi Toilet</h1>
        <p class="text-xs text-gray-600 mt-1">Dibuat pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</p>
        <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
            <div>
                <strong>Periode:</strong> 
                {{ request('date_filter') ? (request('date_filter') === 'today' ? 'Hari Ini' : (request('date_filter') === 'this_week' ? 'Minggu Ini' : (request('date_filter') === 'this_month' ? 'Bulan Ini' : (request('date_filter') === 'custom' ? 'Rentang Tanggal Kustom' : ucfirst(request('date_filter')))))) : 'Semua Periode' }}
                @if(request('date_filter') === 'custom')
                    ({{ request('start_date') }} s/d {{ request('end_date') }})
                @endif
            </div>
            <div>
                <strong>Filter Aktif:</strong>
                @php
                    $filters = [];
                    if(request('building')) $filters[] = "Gedung: " . request('building');
                    if(request('floor')) $filters[] = "Lantai: " . request('floor');
                    if(request('status')) $filters[] = "Status: " . (request('status') === 'submitted' ? 'Menunggu Ulasan' : (request('status') === 'approved' ? 'Disetujui' : (request('status') === 'rejected' ? 'Ditolak' : request('status'))));
                @endphp
                {{ count($filters) > 0 ? implode(', ', $filters) : 'Tidak ada' }}
            </div>
        </div>
    </div>

    <!-- Filters Container (no-print) -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm mb-6 space-y-4 no-print" id="filter-form-container">
        <form action="{{ route('toilet_inspection.report') }}" method="GET" class="space-y-4" id="filter-form">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari berdasarkan nama petugas, gedung, atau area..."
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label for="date_filter" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Periode Tanggal</label>
                    <select name="date_filter" id="date_filter" onchange="toggleCustomDates(); submitFormWithTurbo(this);"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Periode</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Rentang Tanggal Kustom</option>
                    </select>
                </div>
            </div>

            <!-- Custom Dates Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden" id="custom-dates">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>
            </div>

            <!-- Parameters Filters Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-[#F1F5F9]">
                <div>
                    <label for="status" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Status</label>
                    <select name="status" id="status" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Status</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Ulasan</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="building" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Gedung</label>
                    <select name="building" id="building" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Gedung</option>
                        @foreach ($buildings as $b)
                            <option value="{{ $b }}" {{ request('building') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="floor" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Lantai</label>
                    <select name="floor" id="floor" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Lantai</option>
                        @foreach ($floors as $f)
                            <option value="{{ $f }}" {{ request('floor') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Inspector Filter & Action Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2">
                <div class="w-full sm:w-72">
                    <label for="inspector_id" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Petugas Inspeksi</label>
                    <select name="inspector_id" id="inspector_id" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Petugas Inspeksi</option>
                        @foreach ($inspectors as $ins)
                            <option value="{{ $ins->id }}" {{ request('inspector_id') == $ins->id ? 'selected' : '' }}>{{ $ins->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    <a href="{{ route('toilet_inspection.report') }}"
                        class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#475569] shadow-sm hover:bg-[#F9FAFB] transition-colors text-center w-full sm:w-auto">
                        Hapus Filter
                    </a>
                    <button type="submit"
                        class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors text-center w-full sm:w-auto">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Indicators (no-print) -->
    @if(request('search') || request('date_filter') || request('status') || request('building') || request('floor') || request('inspector_id'))
        <div class="flex flex-wrap gap-2 items-center mb-6 text-xs text-[#475569] no-print">
            <span class="font-bold">Filter Aktif:</span>
            @if(request('search'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Cari: "{{ request('search') }}"</span>
            @endif
            @if(request('date_filter'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">
                    Periode: {{ request('date_filter') === 'custom' ? (request('start_date') . ' sampai ' . request('end_date')) : (request('date_filter') === 'today' ? 'Hari Ini' : (request('date_filter') === 'this_week' ? 'Minggu Ini' : (request('date_filter') === 'this_month' ? 'Bulan Ini' : request('date_filter')))) }}
                </span>
            @endif
            @if(request('status'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Status: {{ request('status') === 'submitted' ? 'Menunggu Ulasan' : (request('status') === 'approved' ? 'Disetujui' : (request('status') === 'rejected' ? 'Ditolak' : 'Draft')) }}</span>
            @endif
            @if(request('building'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Gedung: {{ request('building') }}</span>
            @endif
            @if(request('floor'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Lantai: {{ request('floor') }}</span>
            @endif
            @if(request('inspector_id'))
                @php
                    $activeInspector = $inspectors->firstWhere('id', request('inspector_id'));
                @endphp
                @if($activeInspector)
                    <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Petugas Inspeksi: {{ $activeInspector->name }}</span>
                @endif
            @endif
        </div>
    @endif

    <!-- Summary Metrics (Printed and Displayed) -->
    <div class="stats-grid-print grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm min-w-0">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Total Inspeksi">Total Inspeksi</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $totalInspections }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm min-w-0">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Menunggu Ulasan">Menunggu Ulasan</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $pendingCount }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm min-w-0">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Disetujui">Disetujui</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $approvedCount }}</p>
        </div>
        <div class="stat-card-print bg-white border border-[#E5E7EB] rounded-lg p-4 shadow-sm min-w-0">
            <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate" title="Ditolak">Ditolak</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-0.5 truncate">{{ $rejectedCount }}</p>
        </div>
    </div>

    <!-- Table Listing -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Disetujui Oleh</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider no-print">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($inspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->inspector->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ $ins->location->building }}, {{ $ins->location->floor }} — Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $ins->location->area }})
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
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->approval && $ins->approval->approver ? $ins->approval->approver->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold no-print">
                            <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#64748B]">Tidak ada inspeksi yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Container (no-print) -->
    <div class="mt-6 no-print pagination-container">
        {{ $inspections->links() }}
    </div>
</div>

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
    if (window.onPageLoad) { window.onPageLoad(toggleCustomDates); } else { document.addEventListener('DOMContentLoaded', toggleCustomDates); }
</script>
@endsection
