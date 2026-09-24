@extends('layouts.app')

@section('title', 'Daftar Mobil - SBA System')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                Daftar Mobil
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Data master armada kendaraan operasional PT. Solusi Bangun Andalas.
            </p>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isGa())
            <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors shrink-0">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Tambah Mobil
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

    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('vehicles.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor polisi atau nama mobil..." class="w-full rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-[#CBD5E1] bg-white px-3 py-2 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <option value="">Semua Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Perbaikan</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#F1F5F9] px-4 py-2 text-sm font-semibold text-[#0F172A] hover:bg-[#E2E8F0] transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('vehicles.index') }}" class="text-xs font-medium text-[#64748B] hover:text-[#0F172A] underline">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E2E8F0]">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">No. Polisi</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Nama / Model</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Bahan Bakar</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Catatan</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-[#0F172A] font-mono">{{ $vehicle->plate_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-[#334155]">{{ $vehicle->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#64748B]">{{ $vehicle->fuel_type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($vehicle->status === 'available')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @elseif($vehicle->status === 'in_use')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Digunakan
                                    </span>
                                @elseif($vehicle->status === 'maintenance')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Perbaikan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[#64748B] text-xs max-w-xs truncate">{{ $vehicle->notes ?? '-' }}</td>
                            @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="p-1.5 text-[#64748B] hover:text-[#2563EB] hover:bg-slate-100 rounded-lg transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mobil ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-[#64748B] hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="Hapus">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                                    <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3.5 text-[#2563EB] shadow-xs">
                                        <span class="material-symbols-outlined" style="font-size: 32px;">directions_car</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-[#0F172A] mb-1">
                                        Belum ada data kendaraan yang terdaftar.
                                    </h3>
                                    <p class="text-xs text-[#64748B] mb-4 max-w-sm">
                                        Belum ada armada mobil operasional yang ditambahkan ke dalam sistem inventaris.
                                    </p>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                                        <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                            Tambah Kendaraan Baru
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
            <div class="px-6 py-4 border-t border-[#E2E8F0]">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
