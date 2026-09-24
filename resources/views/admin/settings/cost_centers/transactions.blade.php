@extends('layouts.app')

@section('title', 'Aktivitas & Transaksi - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Cost Center Management
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Jurnal audit riwayat transaksi pengeluaran dan pembalikan biaya.
                </p>
            </div>
        </div>
    </header>

    @include('admin.settings.cost_centers.tabs')

    <main class="space-y-6">
        <!-- Filter Toolbar -->
        <form action="{{ route('admin.settings.cost-centers.transactions') }}" method="GET" class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-xs space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari item, nama, catatan..."
                        class="block w-full rounded-md border border-[#E5E7EB] pl-9 pr-3 py-2 text-[#0F172A] text-xs shadow-xs focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[18px]">search</span>
                    </div>
                </div>

                <!-- Activity Type -->
                <div>
                    <select name="activity_type" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] text-xs shadow-xs focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="all">Semua Aktivitas</option>
                        <option value="ROOM_BOOKING" {{ request('activity_type') === 'ROOM_BOOKING' ? 'selected' : '' }}>Ruang Meeting</option>
                        <option value="ATK_USAGE" {{ request('activity_type') === 'ATK_USAGE' ? 'selected' : '' }}>Penggunaan ATK</option>
                    </select>
                </div>

                <!-- Cost Center -->
                <div>
                    <select name="cost_center_id" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] text-xs shadow-xs focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="all">Semua Cost Center</option>
                        @foreach($costCenters as $cc)
                            <option value="{{ $cc->id }}" {{ request('cost_center_id') == $cc->id ? 'selected' : '' }}>
                                {{ $cc->cost_center_code }} - {{ $cc->name ?: $cc->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <select name="status" onchange="submitFormWithTurbo(this)"
                        class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] text-xs shadow-xs focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                        <option value="all">Semua Status Transaksi</option>
                        <option value="posted" {{ request('status') === 'posted' ? 'selected' : '' }}>Posted (Aktif)</option>
                        <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>Reversed (Pembalikan)</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full bg-[#2563EB] text-white text-xs font-bold py-2 rounded-md hover:bg-[#1D4ED8] transition-colors cursor-pointer">
                        Filter Data
                    </button>
                    @if(request()->anyFilled(['search', 'activity_type', 'cost_center_id', 'status', 'start_date', 'end_date']))
                        <a href="{{ route('admin.settings.cost-centers.transactions') }}" class="p-2 text-slate-500 hover:text-[#0F172A] rounded-md hover:bg-slate-100 transition-colors" title="Reset Filter">
                            <span class="material-symbols-outlined text-[18px]">filter_alt_off</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Date Range Filter Row -->
            <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100 text-xs text-[#64748B]">
                <span class="font-bold">Rentang Tanggal:</span>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="rounded-md border border-[#E5E7EB] px-2.5 py-1 text-xs text-[#0F172A]">
                <span>s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="rounded-md border border-[#E5E7EB] px-2.5 py-1 text-xs text-[#0F172A]">
            </div>
        </form>

        <!-- Transactions Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Tanggal & Jam</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Aktivitas</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Deskripsi & Item</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Cost Center</th>
                            <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-[#64748B] uppercase tracking-wider">Pengguna</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Qty / Durasi</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Tarif Satuan</th>
                            <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-[#64748B] uppercase tracking-wider">Total Biaya</th>
                            <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-[#64748B] uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm font-medium">
                        @forelse ($transactions as $tx)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-[#64748B]">
                                    {{ $tx->created_at->format('d/m/Y') }}
                                    <div class="text-[11px] text-slate-400">{{ $tx->created_at->format('H:i') }} WIB</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($tx->activity_type === 'ROOM_BOOKING')
                                        <span class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-600/20">
                                            <span class="material-symbols-outlined text-[14px]">meeting_room</span>
                                            Ruang Meeting
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                            ATK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="font-bold text-[#0F172A]">{{ $tx->item_name }}</div>
                                    <div class="text-[#64748B] text-[11px] mt-0.5">{{ $tx->description }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs">
                                    <div class="font-bold text-[#2563EB]">{{ $tx->costCenter ? $tx->costCenter->cost_center_code : '-' }}</div>
                                    <div class="text-[#64748B] text-[11px] truncate max-w-[140px]">{{ $tx->costCenter ? ($tx->costCenter->name ?: $tx->costCenter->description) : '' }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs text-[#0F172A]">
                                    {{ $tx->user ? $tx->user->name : '-' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold text-[#64748B]">
                                    {{ $tx->activity_type === 'ROOM_BOOKING' ? number_format($tx->quantity, 1) . ' jam' : number_format($tx->quantity, 0) . ' unit' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-semibold text-[#64748B]">
                                    Rp {{ number_format((float)$tx->unit_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right font-black text-sm {{ $tx->total_amount < 0 ? 'text-rose-600' : 'text-[#0F172A]' }}">
                                    Rp {{ number_format((float)$tx->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-center">
                                    @if ($tx->status === 'posted')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            Posted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                            Reversed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400 italic">
                                    Tidak ada transaksi yang cocok dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </main>
</div>
@endsection
