@extends('layouts.app')

@section('title', 'Cost Center Overview - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Cost Center Management
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Pemantauan alokasi biaya reservasi ruang meeting dan penggunaan ATK per departemen.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings.cost-centers.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add</span> Tambah Cost Center
                </a>
            </div>
        </div>
    </header>

    @include('admin.settings.cost_centers.tabs')

    <main class="space-y-6">
        <!-- Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Total Spend -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Total Pengeluaran</span>
                        <div class="p-2 rounded-lg bg-[#EFF6FF] text-[#2563EB]">
                            <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-[#0F172A] mt-2">
                        Rp {{ number_format($totalExpenditure, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-[#64748B]">Bulan ini: Rp {{ number_format($thisMonthSpend, 0, ',', '.') }}</span>
                    @if($momPercentage > 0)
                        <span class="inline-flex items-center font-bold text-amber-600">
                            <span class="material-symbols-outlined text-[14px]">trending_up</span> +{{ $momPercentage }}%
                        </span>
                    @elseif($momPercentage < 0)
                        <span class="inline-flex items-center font-bold text-emerald-600">
                            <span class="material-symbols-outlined text-[14px]">trending_down</span> {{ $momPercentage }}%
                        </span>
                    @else
                        <span class="text-slate-400">0% MoM</span>
                    @endif
                </div>
            </div>

            <!-- Meeting Room Spend -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Biaya Ruang Meeting</span>
                        <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                            <span class="material-symbols-outlined text-[20px]">meeting_room</span>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-[#0F172A] mt-2">
                        Rp {{ number_format($roomExpenditure, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-[#64748B]">
                    <span>Porsi Reservasi</span>
                    <span class="font-bold text-indigo-600">
                        {{ $totalExpenditure > 0 ? round(($roomExpenditure / $totalExpenditure) * 100, 1) : 0 }}%
                    </span>
                </div>
            </div>

            <!-- ATK Usage Spend -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Biaya Penggunaan ATK</span>
                        <div class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                            <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-[#0F172A] mt-2">
                        Rp {{ number_format($atkExpenditure, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-[#64748B]">
                    <span>Porsi ATK</span>
                    <span class="font-bold text-emerald-600">
                        {{ $totalExpenditure > 0 ? round(($atkExpenditure / $totalExpenditure) * 100, 1) : 0 }}%
                    </span>
                </div>
            </div>

            <!-- Transactions Count & Active Units -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Total Transaksi</span>
                        <div class="p-2 rounded-lg bg-amber-50 text-amber-600">
                            <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-[#0F172A] mt-2">
                        {{ number_format($totalTransactionsCount, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-[#64748B]">
                    <span>Cost Center Aktif:</span>
                    <span class="font-bold text-[#0F172A]">{{ $activeCostCentersCount }} unit</span>
                </div>
            </div>
        </div>

        <!-- 2 Column Section: Top Cost Centers & Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Top Cost Centers Ranking -->
            <div class="lg:col-span-5 bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-xs">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-[#0F172A] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#2563EB] text-[20px]">leaderboard</span>
                        Top Pengeluaran Cost Center
                    </h2>
                    <a href="{{ route('admin.settings.cost-centers.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($topCostCenters as $cc)
                        @php
                            $spend = (float) $cc->transactions_sum_total_amount;
                            $pct = $totalExpenditure > 0 ? min(100, round(($spend / $totalExpenditure) * 100, 1)) : 0;
                        @endphp
                        <div class="p-3.5 rounded-lg border border-slate-100 hover:bg-[#F8FAFC] transition-colors">
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-bold text-[#0F172A] truncate max-w-[200px]" title="{{ $cc->description }}">
                                    {{ $cc->cost_center_code }} - {{ $cc->name ?: $cc->description }}
                                </span>
                                <span class="font-bold text-[#2563EB]">
                                    Rp {{ number_format($spend, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-[#2563EB] h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-[#64748B] mt-1.5">
                                <span>PJ: {{ $cc->person_responsible }}</span>
                                <span>{{ $pct }}% dari total</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-[#64748B]">
                            <span class="material-symbols-outlined text-[40px] text-slate-300">receipt</span>
                            <p class="mt-2 text-sm">Belum ada data pengeluaran Cost Center.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="lg:col-span-7 bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-base font-bold text-[#0F172A] flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#2563EB] text-[20px]">history</span>
                            Transaksi Terkini
                        </h2>
                        <a href="{{ route('admin.settings.cost-centers.transactions') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                            Lihat Semua Transaksi
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                            <thead class="bg-slate-50 text-[#64748B] uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">Aktivitas</th>
                                    <th class="py-2.5 px-3">Cost Center</th>
                                    <th class="py-2.5 px-3">Pengguna</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @forelse($recentTransactions as $tx)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-3 text-[#64748B] whitespace-nowrap">
                                            {{ $tx->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-3 px-3 text-[#0F172A] whitespace-nowrap">
                                            @if($tx->activity_type === 'ROOM_BOOKING')
                                                <span class="inline-flex items-center gap-1 font-bold text-indigo-700">
                                                    <span class="material-symbols-outlined text-[14px]">meeting_room</span>
                                                    Ruang Meeting
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700">
                                                    <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                                    ATK
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 font-semibold text-[#2563EB] whitespace-nowrap">
                                            {{ $tx->costCenter ? $tx->costCenter->cost_center_code : '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-[#0F172A] whitespace-nowrap">
                                            {{ $tx->user ? $tx->user->name : '-' }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold whitespace-nowrap {{ $tx->total_amount < 0 ? 'text-rose-600' : 'text-[#0F172A]' }}">
                                            Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-center whitespace-nowrap">
                                            @if($tx->status === 'posted')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    Posted
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                                    Reversed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-[#64748B]">
                                            Belum ada transaksi tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
