@extends('layouts.app')

@section('title', 'Konsumsi BBM - SBA System')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                Konsumsi BBM
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Pencatatan realisasi pengisian bahan bakar dan riwayat konsumsi (km/L) armada kendaraan operasional.
            </p>
        </div>
        @if(auth()->user()->isGa())
        <a href="{{ route('vehicles.fuel.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors shrink-0">
            <span class="material-symbols-outlined text-[20px]">add</span>
            Tambah Pengisian BBM
        </a>
        @endif
    </header>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200">
            <div class="flex items-center gap-2 text-sm font-medium text-emerald-800">
                <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200">
            <div class="flex items-center gap-2 text-sm font-medium text-rose-800">
                <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6">
        <form method="GET" action="{{ route('vehicles.fuel.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama mobil, plat, atau catatan..." class="w-full rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>
            
            <div class="min-w-[180px]">
                <select name="vehicle_id" onchange="this.form.submit()" class="w-full rounded-xl border border-[#CBD5E1] bg-white px-3 py-2 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                    <option value="">Semua Kendaraan</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->name }} ({{ $vehicle->plate_number }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" title="Mulai Tanggal" class="rounded-xl border border-[#CBD5E1] bg-white px-3 py-1.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <span class="text-xs text-[#64748B]">s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" title="Sampai Tanggal" class="rounded-xl border border-[#CBD5E1] bg-white px-3 py-1.5 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>

            <button type="submit" class="rounded-xl bg-[#F1F5F9] px-4 py-2 text-sm font-semibold text-[#0F172A] hover:bg-[#E2E8F0] transition-colors cursor-pointer">
                Filter
            </button>

            @if(request()->hasAny(['search', 'vehicle_id', 'start_date', 'end_date']))
                <a href="{{ route('vehicles.fuel.index') }}" class="text-xs font-medium text-[#64748B] hover:text-[#0F172A] underline">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E2E8F0]">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Tanggal & Waktu</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Kendaraan</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Odometer</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">BBM (Liter)</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Biaya</th>
                        <th scope="col" class="px-6 py-3.5 text-center text-xs font-semibold text-[#64748B] uppercase tracking-wider">Konsumsi</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Dicatat Oleh</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($fuelLogs as $log)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#0F172A] font-medium">
                                <div>{{ $log->recorded_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-[#64748B]">{{ $log->recorded_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-[#0F172A]">{{ $log->vehicle?->name ?? '-' }}</div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono text-xs text-[#64748B]">{{ $log->vehicle?->plate_number ?? '-' }}</span>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700">
                                        {{ $log->vehicle?->fuel_type ?? 'BBM' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-[#0F172A]">
                                {{ number_format($log->odometer, 0, ',', '.') }} km
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
                                    <span class="text-slate-400 font-semibold" title="Pengisian awal atau data pengisian belum dapat dihitung">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#64748B]">
                                <div class="font-medium text-[#334155]">{{ $log->recorder?->name ?? 'System' }}</div>
                                @if($log->notes)
                                    <div class="text-[11px] text-slate-400 truncate max-w-xs" title="{{ $log->notes }}">{{ $log->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($log->vehicle)
                                        <a href="{{ route('vehicles.fuel.history', $log->vehicle) }}" class="p-1.5 text-[#64748B] hover:text-[#2563EB] hover:bg-slate-100 rounded-lg transition-colors" title="Lihat Histori Armada">
                                            <span class="material-symbols-outlined text-[18px]">history</span>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isGa())
                                        <a href="{{ route('vehicles.fuel.edit', $log) }}" class="p-1.5 text-[#64748B] hover:text-[#2563EB] hover:bg-slate-100 rounded-lg transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('vehicles.fuel.destroy', $log) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan pengisian BBM ini? Nilai konsumsi pengisian berikutnya akan dihitung ulang secara otomatis.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-[#64748B] hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer" title="Hapus">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                                    <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3.5 text-[#2563EB] shadow-xs">
                                        <span class="material-symbols-outlined" style="font-size: 32px;">local_gas_station</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-[#0F172A] mb-1">
                                        Belum ada catatan pengisian BBM yang tersimpan.
                                    </h3>
                                    <p class="text-xs text-[#64748B] mb-4 max-w-sm">
                                        Belum ada data realisasi pengisian bahan bakar armada operasional yang tersimpan atau sesuai kriteria filter.
                                    </p>
                                    @if(auth()->user()->isGa())
                                        <a href="{{ route('vehicles.fuel.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                            Tambah Pengisian Sekarang
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
