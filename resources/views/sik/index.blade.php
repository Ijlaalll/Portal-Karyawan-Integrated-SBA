@extends('layouts.app')

@section('title', 'SIK Saya - Surat Izin Keluar SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Surat Izin Keluar (SIK) Saya
                </h1>
                <p class="text-sm text-[#475569] mt-1">Kelola dan pantau seluruh permohonan izin keluar pabrik Anda.</p>
            </div>
            <a href="{{ route('sik.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors self-start sm:self-auto">
                <span class="material-symbols-outlined text-[20px]">add_circle</span>
                Ajukan SIK Baru
            </a>
        </div>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600 text-[22px]">check_circle</span>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 p-4 border border-red-200 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600 text-[22px]">error</span>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3.5 sm:gap-4 mb-6">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #2563EB !important;">
                <p class="text-[11px] font-bold text-[#2563EB] uppercase tracking-wider">Total SIK</p>
                <p class="text-2xl font-bold text-[#0F172A] mt-1.5">{{ $totalCount }}</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #64748B !important;">
                <p class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Draft</p>
                <p class="text-2xl font-bold text-slate-700 mt-1.5">{{ $draftCount }}</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #F59E0B !important;">
                <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-amber-600 mt-1.5">{{ $pendingCount }}</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #4F46E5 !important;">
                <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Disetujui</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1.5">{{ $approvedCount }}</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #9333EA !important;">
                <p class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Di Luar</p>
                <p class="text-2xl font-bold text-purple-600 mt-1.5">{{ $outsideCount }}</p>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-2xs hover:shadow-xs transition-shadow" style="border-left: 4px solid #059669 !important;">
                <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Selesai/Arsip</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1.5">{{ $historyCount }}</p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6 shadow-sm">
            <form action="{{ route('sik.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor SIK atau keperluan..."
                        class="block w-full rounded-lg border border-[#E5E7EB] pl-10 pr-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">

                    <select name="purpose_type" class="rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                        <option value="">Semua Jenis</option>
                        <option value="dinas" {{ request('purpose_type') === 'dinas' ? 'selected' : '' }}>Dinas</option>
                        <option value="pribadi" {{ request('purpose_type') === 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                    </select>

                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition-colors">
                        Filter
                    </button>

                    @if(request()->filled('search') || request()->filled('date') || request()->filled('purpose_type'))
                        <a href="{{ route('sik.index', ['tab' => $tab]) }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- SIK Table List -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs font-semibold text-[#475569] uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nomor SIK</th>
                            <th scope="col" class="px-6 py-4">Tanggal & Jam</th>
                            <th scope="col" class="px-6 py-4">Jenis & Keperluan</th>
                            <th scope="col" class="px-6 py-4">Approver</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white">
                        @forelse ($permits as $permit)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-[#0F172A] font-mono text-xs">{{ $permit->sik_number }}</span>
                                    <div class="text-[11px] text-[#64748B] mt-0.5">{{ $permit->department }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-[#0F172A]">{{ $permit->permit_date->format('d M Y') }}</div>
                                    <div class="text-xs text-[#64748B] mt-0.5">
                                        {{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }} WIB
                                    </div>
                                </td>
                                <td class="px-6 py-4 max-w-[260px]">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $permit->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                            {{ $permit->purpose_type }}
                                        </span>
                                        <p class="font-medium text-slate-800 text-xs leading-snug line-clamp-2" title="{{ $permit->purpose }}">
                                            {{ $permit->purpose }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-xs font-medium text-[#0F172A]">{{ $permit->approver ? $permit->approver->name : '-' }}</div>
                                    @if($permit->approved_at)
                                        <div class="text-[10px] text-green-700 mt-0.5">Disetujui: {{ $permit->approved_at->format('d/m/y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $effStatus = $permit->effective_status;
                                    @endphp
                                    @if($effStatus === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            DRAFT
                                        </span>
                                    @elseif($effStatus === 'pending_approval')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            MENUNGGU APPROVAL
                                        </span>
                                    @elseif($effStatus === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            DISETUJUI / SIAP
                                        </span>
                                    @elseif($effStatus === 'outside')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                            SEDANG DI LUAR
                                        </span>
                                    @elseif($effStatus === 'overdue')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                                            TERLAMBAT (OVERDUE)
                                        </span>
                                    @elseif($effStatus === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            SUDAH KEMBALI
                                        </span>
                                    @elseif($effStatus === 'closed_manually')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                                            TUTUP MANUAL
                                        </span>
                                    @elseif($effStatus === 'revoked')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            DICABUT (REVOKED)
                                        </span>
                                    @elseif($effStatus === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            DITOLAK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <a href="{{ route('sik.show', $permit) }}" class="inline-flex items-center text-[#2563EB] hover:text-[#1D4ED8] font-semibold">
                                        Detail
                                    </a>

                                    @if($permit->isDraft())
                                        <a href="{{ route('sik.edit', $permit) }}" class="text-slate-600 hover:text-slate-900 font-semibold">
                                            Edit
                                        </a>
                                        <form action="{{ route('sik.submit', $permit) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 font-semibold">
                                                Kirim
                                            </button>
                                        </form>
                                    @endif

                                    @if($permit->canBeCancelledBy(auth()->user()))
                                        <form action="{{ route('sik.cancel', $permit) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan SIK ini?')">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">assignment_late</span>
                                    Belum ada data Surat Izin Keluar (SIK) pada tab ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($permits->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $permits->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
