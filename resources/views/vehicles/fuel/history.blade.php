@extends('layouts.app')

@section('title', 'Histori BBM ' . $vehicle->name . ' - SBA System')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('vehicles.fuel.index') }}" class="p-1 rounded-lg text-[#64748B] hover:text-[#0F172A] hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                    Histori BBM: {{ $vehicle->name }}
                </h1>
            </div>
            <p class="text-sm text-[#64748B] mt-1 ml-7">
                Riwayat kronologis pengisian bahan bakar dan perkembangan konsumsi (km/L).
            </p>
        </div>
        @if(auth()->user()->isGa())
        <div class="flex items-center gap-2.5">
            <a href="{{ route('vehicles.fuel.create', ['vehicle_id' => $vehicle->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors shrink-0">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Tambah Pengisian
            </a>
        </div>
        @endif
    </div>

    <!-- Vehicle Summary Card -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 mb-6 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">directions_car</span>
            </div>
            <div>
                <div class="text-base font-bold text-[#0F172A]">{{ $vehicle->name }}</div>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-[#64748B]">
                    <span class="font-mono font-semibold">{{ $vehicle->plate_number }}</span>
                    <span>•</span>
                    <span>Bahan Bakar: <strong class="text-slate-800">{{ $vehicle->fuel_type }}</strong></span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <div class="text-xs text-[#64748B]">Total Riwayat</div>
                <div class="text-lg font-bold text-[#0F172A]">{{ $fuelLogs->total() }} Pengisian</div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200">
            <div class="flex items-center gap-2 text-sm font-medium text-emerald-800">
                <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    <!-- History Table -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E2E8F0]">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Tanggal & Waktu</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Odometer</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Jarak Tempuh</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">BBM (Liter)</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Biaya</th>
                        <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-[#64748B] uppercase tracking-wider">Konsumsi</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Dicatat Oleh</th>
                        @if(auth()->user()->isGa())
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($fuelLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#0F172A] font-medium">
                                <div>{{ $log->recorded_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $log->recorded_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-[#0F172A]">
                                {{ number_format($log->odometer, 0, ',', '.') }} km
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-[#64748B]">
                                @if($log->distance !== null && $log->distance > 0)
                                    +{{ number_format($log->distance, 0, ',', '.') }} km
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-[#334155]">
                                {{ number_format($log->fuel_liters, 2, ',', '.') }} L
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-[#64748B]">
                                @if($log->fuel_cost !== null)
                                    Rp {{ number_format($log->fuel_cost, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($log->consumption !== null)
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $log->consumption }} km/L
                                    </span>
                                @else
                                    <span class="text-slate-400 font-semibold" title="Pengisian pertama atau belum tersedia">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#64748B]">
                                <div class="font-medium text-[#334155]">{{ $log->recorder?->name ?? 'System' }}</div>
                                @if($log->notes)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs" title="{{ $log->notes }}">{{ $log->notes }}</div>
                                @endif
                            </td>
                            @if(auth()->user()->isGa())
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('vehicles.fuel.edit', $log) }}" class="p-1.5 text-[#64748B] hover:text-[#2563EB] hover:bg-slate-100 rounded-lg transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>
                                    <form action="{{ route('vehicles.fuel.destroy', $log) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pengisian BBM ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-[#64748B] hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer" title="Hapus">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isGa() ? 8 : 7 }}" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                                    <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3.5 text-[#2563EB] shadow-xs">
                                        <span class="material-symbols-outlined" style="font-size: 32px;">local_gas_station</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-[#0F172A] mb-1">
                                        Belum ada riwayat pengisian BBM untuk kendaraan ini.
                                    </h3>
                                    <p class="text-xs text-[#64748B] mb-4 max-w-sm">
                                        Belum ada catatan pengisian bahan bakar yang tercatat secara kronologis pada armada ini.
                                    </p>
                                    @if(auth()->user()->isGa())
                                        <a href="{{ route('vehicles.fuel.create', ['vehicle_id' => $vehicle->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                            Catat Pengisian Pertama
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($fuelLogs->hasPages())
            <div class="px-6 py-4 border-t border-[#E2E8F0]">
                {{ $fuelLogs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
