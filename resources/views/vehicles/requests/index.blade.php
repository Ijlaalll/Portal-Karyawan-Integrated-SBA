@extends('layouts.app')

@section('title', $isAdmin ? 'Monitoring Permohonan Kendaraan - SBA System' : ($isGa ? 'Daftar Permohonan Kendaraan - SBA System' : 'Permohonan Kendaraan Saya - SBA System'))

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#0F172A]">
                @if($isAdmin)
                    Monitoring Permohonan Kendaraan
                @elseif($isGa)
                    Daftar Permohonan Kendaraan
                @else
                    Permohonan Kendaraan Saya
                @endif
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                @if($isAdmin)
                    Pantau seluruh data permohonan kendaraan operasional perusahaan secara global.
                @elseif($isGa)
                    Pantau dan proses persetujuan serta alokasi armada dan supir operasional.
                @else
                    Kelola dan pantau status permohonan peminjaman kendaraan Anda.
                @endif
            </p>
        </div>
        @if(!$isAdmin && !$isGa)
            <a href="{{ route('vehicles.requests.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors shrink-0">
                <span class="material-symbols-outlined text-[20px]">add</span>
                Ajukan Kendaraan
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
        <form method="GET" action="{{ route('vehicles.requests.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <div class="relative flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. permohonan, pemohon, keperluan, atau tujuan..." class="w-full rounded-xl border border-[#CBD5E1] bg-white px-4 py-2 text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
            </div>
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-[#CBD5E1] bg-white px-3 py-2 text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]">
                <option value="">Semua Status</option>
                <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
            <button type="submit" class="rounded-xl bg-[#F1F5F9] px-4 py-2 text-sm font-semibold text-[#0F172A] hover:bg-[#E2E8F0] transition-colors">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('vehicles.requests.index') }}" class="text-xs font-medium text-[#64748B] hover:text-[#0F172A] underline">Reset</a>
            @endif
        </form>
    </div>

    @if($requests->isEmpty())
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-8 sm:p-12 text-center shadow-xs">
            <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3.5 text-[#2563EB] shadow-xs">
                    <span class="material-symbols-outlined" style="font-size: 32px;">commute</span>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-[#0F172A] mb-1">
                    Belum ada permohonan kendaraan yang diajukan.
                </h3>
                <p class="text-xs sm:text-sm text-[#64748B] mb-5 max-w-sm">
                    {{ $isManager ? 'Belum ada permohonan penggunaan armada kendaraan operasional yang masuk.' : 'Anda belum memiliki riwayat permohonan kendaraan operasional.' }}
                </p>
                @if(!$isManager)
                    <a href="{{ route('vehicles.requests.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-[#2563EB] px-5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Ajukan Permohonan Kendaraan
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white border border-[#E2E8F0] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] divide-y divide-[#E2E8F0]">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">No. Permohonan</th>
                        @if($isManager)
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Pemohon</th>
                        @endif
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Keperluan & Tujuan</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Jadwal Perjalanan</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Armada & Supir</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#64748B] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#64748B] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] text-sm">
                    @forelse ($requests as $req)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-bold text-[#0F172A]">
                                <a href="{{ route('vehicles.requests.show', $req) }}" class="text-[#2563EB] hover:underline">
                                    {{ $req->request_number }}
                                </a>
                            </td>
                            @if($isManager)
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-[#0F172A]">{{ $req->user->name }}</div>
                                    <div class="text-xs text-[#64748B]">{{ $req->user->department ?? '-' }}</div>
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <div class="font-semibold text-[#0F172A] max-w-xs truncate">{{ $req->purpose }}</div>
                                <div class="text-xs text-[#64748B] flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-[14px]">location_on</span>
                                    <span class="truncate max-w-[200px]">{{ $req->destination }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-[#334155]">
                                <div class="font-medium">{{ $req->start_datetime->format('d M Y, H:i') }}</div>
                                <div class="text-[#64748B]">s/d {{ $req->end_datetime->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                @if($req->vehicle)
                                    <div class="font-bold text-[#0F172A]">{{ $req->vehicle->name }}</div>
                                    <div class="text-[#64748B] font-mono text-[11px]">{{ $req->vehicle->plate_number }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum dialokasikan</span>
                                @endif

                                @if($req->driver)
                                    <div class="text-emerald-700 text-[11px] mt-1 flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[13px]">person</span>
                                        {{ $req->driver->name }}
                                    </div>
                                @elseif(in_array($req->status, ['approved', 'completed']))
                                    <div class="text-slate-500 text-[11px] mt-1 flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-[13px]">key</span>
                                        Lepas Kunci (Tanpa Supir)
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($req->status === 'pending_approval')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Menunggu Persetujuan
                                    </span>
                                @elseif($req->status === 'approved')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Disetujui
                                    </span>
                                @elseif($req->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        Ditolak
                                    </span>
                                @elseif($req->status === 'completed')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                        Selesai
                                    </span>
                                @elseif($req->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('vehicles.requests.show', $req) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-[#334155] hover:bg-[#2563EB] hover:text-white transition-colors">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-[#E2E8F0]">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
@endsection
