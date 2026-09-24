@extends('layouts.app')

@section('title', 'Riwayat Inspeksi Toilet - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Riwayat Inspeksi Toilet
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Telusuri dan filter riwayat catatan inspeksi toilet.
        </p>
    </header>

    <!-- Search & Filters Container -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm mb-6 space-y-4">
        <form action="{{ route('inspections.index') }}" method="GET" class="space-y-4" id="filter-form">
            <!-- Search & Date Filter Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Cari</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Cari berdasarkan nama petugas, gedung, atau area..."
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                </div>

                <div>
                    <label for="date_filter" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Periode Tanggal</label>
                    <select name="date_filter" id="date_filter" onchange="toggleCustomDates(); this.form.submit();"
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ request('date_filter') === 'custom' ? '' : 'hidden' }}" id="custom-dates">
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
                    <select name="status" id="status" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Status</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Ulasan</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div>
                    <label for="building" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Gedung</label>
                    <select name="building" id="building" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Gedung</option>
                        @foreach($buildings as $bld)
                            <option value="{{ $bld }}" {{ request('building') === $bld ? 'selected' : '' }}>{{ $bld }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="floor" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Lantai</label>
                    <select name="floor" id="floor" onchange="this.form.submit()"
                        class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Lantai</option>
                        @foreach($floors as $flr)
                            <option value="{{ $flr }}" {{ request('floor') === $flr ? 'selected' : '' }}>{{ $flr }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Inspector Filter (Admin/SPV only) & Action Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2">
                @if(auth()->user()->isAdmin() || auth()->user()->isSpv())
                    <div class="w-full sm:w-72">
                        <label for="inspector_id" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1.5">Petugas Inspeksi</label>
                        <select name="inspector_id" id="inspector_id" onchange="this.form.submit()"
                            class="block w-full rounded-md border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                            <option value="">Semua Petugas Inspeksi</option>
                            @foreach($inspectors as $ins)
                                <option value="{{ $ins->id }}" {{ request('inspector_id') == $ins->id ? 'selected' : '' }}>{{ $ins->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div></div>
                @endif

                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    @if(request()->anyFilled(['search', 'status', 'building', 'floor', 'inspector_id', 'date_filter']))
                        <a href="{{ route('inspections.index') }}"
                            class="rounded border border-[#E5E7EB] bg-white px-4 py-2 text-sm font-semibold text-[#475569] shadow-sm hover:bg-[#F9FAFB] transition-colors text-center w-full sm:w-auto">
                            Hapus Filter
                        </a>
                    @endif
                    <button type="submit"
                        class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors text-center w-full sm:w-auto">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Filters Indicators -->
    @if(request()->anyFilled(['search', 'status', 'building', 'floor', 'inspector_id', 'date_filter']))
        <div class="flex flex-wrap gap-2 items-center mb-6 text-xs text-[#475569]">
            <span class="font-bold">Filter Aktif:</span>
            @if(request('search'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Cari: "{{ request('search') }}"</span>
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
            @if(request('inspector_id') && isset($inspectors))
                @php $selectedIns = $inspectors->firstWhere('id', request('inspector_id')); @endphp
                @if($selectedIns)
                    <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">Petugas Inspeksi: {{ $selectedIns->name }}</span>
                @endif
            @endif
            @if(request('date_filter'))
                <span class="bg-[#F1F5F9] border border-[#E2E8F0] px-2 py-0.5 rounded">
                    Periode: {{ request('date_filter') === 'custom' ? (request('start_date') . ' sampai ' . request('end_date')) : (request('date_filter') === 'today' ? 'Hari Ini' : (request('date_filter') === 'this_week' ? 'Minggu Ini' : (request('date_filter') === 'this_month' ? 'Bulan Ini' : request('date_filter')))) }}
                </span>
            @endif
        </div>
    @endif

    <!-- Table / Log Listing -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gedung</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lantai</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Area / Kantor</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Disetujui Oleh</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($inspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ $ins->location->building }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->location->floor }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->location->area }} (Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">
                            {{ $ins->inspector->name }}
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
                            {{ $ins->approval ? $ins->approval->approver->name : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                            <a href="{{ route('inspections.show', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                Lihat Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-[#64748B]">Tidak ada inspeksi yang ditemukan.</td>
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
    function toggleCustomDates() {
        const select = document.getElementById('date_filter');
        const customDatesDiv = document.getElementById('custom-dates');
        if (select.value === 'custom') {
            customDatesDiv.classList.remove('hidden');
        } else {
            customDatesDiv.classList.add('hidden');
        }
    }
</script>
@endsection
