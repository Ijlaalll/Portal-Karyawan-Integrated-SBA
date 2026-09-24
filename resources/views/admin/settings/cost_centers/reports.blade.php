@extends('layouts.app')

@section('title', 'Laporan Cost Center - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Cost Center Management
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Laporan analitik agregasi biaya pemakaian ruang meeting dan ATK per periode.
                </p>
            </div>
        </div>
    </header>

    @include('admin.settings.cost_centers.tabs')

    <main class="space-y-6">
        <!-- Date & Unit Filter -->
        <form action="{{ route('admin.settings.cost-centers.reports') }}" method="GET" class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-xs text-[#0F172A] shadow-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-xs text-[#0F172A] shadow-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Filter Cost Center</label>
                    <select name="cost_center_id" class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-xs text-[#0F172A] shadow-xs">
                        <option value="all">Semua Cost Center</option>
                        @foreach($costCenters as $cc)
                            <option value="{{ $cc->id }}" {{ $selectedCostCenterId == $cc->id ? 'selected' : '' }}>
                                {{ $cc->cost_center_code }} - {{ $cc->name ?: $cc->description }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-[#2563EB] text-white text-xs font-bold py-2.5 rounded-md hover:bg-[#1D4ED8] transition-colors shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">query_stats</span>
                        Tampilkan Laporan
                    </button>
                </div>
            </div>
        </form>

        <!-- KPI Metrics for filtered range -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Total Beban Biaya</span>
                <p class="text-2xl font-black text-[#0F172A] mt-2">
                    Rp {{ number_format($totalSpend, 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Periode Terpilih</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Beban Ruang Meeting</span>
                <p class="text-2xl font-black text-indigo-600 mt-2">
                    Rp {{ number_format($roomSpend, 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ $totalSpend > 0 ? round(($roomSpend / $totalSpend) * 100, 1) : 0 }}% dari total</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Beban Pemakaian ATK</span>
                <p class="text-2xl font-black text-emerald-600 mt-2">
                    Rp {{ number_format($atkSpend, 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">{{ $totalSpend > 0 ? round(($atkSpend / $totalSpend) * 100, 1) : 0 }}% dari total</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Jumlah Transaksi</span>
                <p class="text-2xl font-black text-[#0F172A] mt-2">
                    {{ number_format($txCount, 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-400 mt-1">Transaksi Jurnal</p>
            </div>
        </div>

        <!-- 2 Breakdown Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Cost Center Breakdown -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-[20px]">account_tree</span>
                    Rekapitulasi per Cost Center
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-[#64748B] uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Cost Center</th>
                                <th class="py-2.5 px-3 text-center">Transaksi</th>
                                <th class="py-2.5 px-3 text-right">Total Biaya</th>
                                <th class="py-2.5 px-3 text-right">Porsi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($costCenterBreakdown as $row)
                                @php
                                    $pct = $totalSpend > 0 ? round(($row->total_spent / $totalSpend) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-[#0F172A]">{{ $row->costCenter ? $row->costCenter->cost_center_code : '-' }}</div>
                                        <div class="text-[#64748B] text-[11px]">{{ $row->costCenter ? ($row->costCenter->name ?: $row->costCenter->description) : '' }}</div>
                                    </td>
                                    <td class="py-3 px-3 text-center text-[#64748B]">
                                        {{ $row->tx_count }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-[#0F172A]">
                                        Rp {{ number_format((float)$row->total_spent, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-[#2563EB]">
                                        {{ $pct }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 italic">
                                        Tidak ada transaksi pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Item / Room Breakdown -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-xs">
                <h2 class="text-base font-bold text-[#0F172A] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#2563EB] text-[20px]">category</span>
                    Top 10 Pengeluaran per Ruang & ATK
                </h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-[#64748B] uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-2.5 px-3">Item / Ruangan</th>
                                <th class="py-2.5 px-3">Tipe</th>
                                <th class="py-2.5 px-3 text-right">Qty</th>
                                <th class="py-2.5 px-3 text-right">Total Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($itemBreakdown as $row)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-3 font-bold text-[#0F172A]">
                                        {{ $row->item_name }}
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($row->activity_type === 'ROOM_BOOKING')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                                Ruang
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                                ATK
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right text-[#64748B]">
                                        {{ $row->activity_type === 'ROOM_BOOKING' ? number_format($row->total_qty, 1) . ' jam' : number_format($row->total_qty, 0) . ' unit' }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-bold text-[#0F172A]">
                                        Rp {{ number_format((float)$row->total_spent, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400 italic">
                                        Tidak ada data pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
