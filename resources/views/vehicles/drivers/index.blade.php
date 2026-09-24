@extends('layouts.app')

@section('title', 'Daftar Supir - SBA System')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                Daftar Supir
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Data master pengemudi operasional kendaraan PT. Solusi Bangun Andalas.
            </p>
        </div>
        @if(auth()->user()->isAdmin() || auth()->user()->isGa())
            <a href="{{ route('vehicles.drivers.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors shrink-0">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Tambah Supir
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
        <form method="GET" action="{{ route('vehicles.drivers.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau nomor telepon supir..." class="w-full rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-[#CBD5E1] bg-white px-3 py-2 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <option value="">Semua Status</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#F1F5F9] px-4 py-2 text-sm font-semibold text-[#0F172A] hover:bg-[#E2E8F0] transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('vehicles.drivers.index') }}" class="text-xs font-medium text-[#64748B] hover:text-[#0F172A] underline">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E2E8F0]">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Nama</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">No. Telepon / WA</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Akun Sistem Terkait</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Catatan</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($drivers as $driver)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-[#0F172A]">{{ $driver->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-[#334155]">{{ $driver->phone_number }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#64748B]">
                                @if($driver->user)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200">
                                        <span class="material-symbols-outlined text-[14px]">person</span>
                                        {{ $driver->user->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Non-akun / Lapangan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($driver->status === 'available')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[#64748B] text-xs max-w-xs truncate">{{ $driver->notes ?? '-' }}</td>
                            @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('vehicles.drivers.edit', $driver) }}" class="p-1.5 text-[#64748B] hover:text-[#2563EB] hover:bg-slate-100 rounded-lg transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </a>
                                        <form action="{{ route('vehicles.drivers.destroy', $driver) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supir ini?');" class="inline">
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
                                        <span class="material-symbols-outlined" style="font-size: 32px;">badge</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-[#0F172A] mb-1">
                                        Belum ada data supir yang terdaftar.
                                    </h3>
                                    <p class="text-xs text-[#64748B] mb-4 max-w-sm">
                                        Belum ada supir armada operasional yang terdaftar dalam sistem.
                                    </p>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isGa())
                                        <a href="{{ route('vehicles.drivers.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">add</span>
                                            Tambah Supir Baru
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($drivers->hasPages())
            <div class="px-6 py-4 border-t border-[#E2E8F0]">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
