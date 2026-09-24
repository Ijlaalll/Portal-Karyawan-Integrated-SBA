@extends('layouts.app')

@section('title', 'Riwayat SIK Departemen - SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Riwayat & Arsip SIK Departemen
                </h1>
                <p class="text-sm text-[#475569] mt-1">Daftar arsip izin keluar yang telah selesai, ditutup, dicabut, atau ditolak.</p>
            </div>
            <a href="{{ route('sik.department.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start sm:self-auto">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                Kembali ke Monitoring
            </a>
        </div>
    </header>

    <main>
        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6 shadow-sm">
            <form action="{{ route('sik.history.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pemohon, nomor SIK, atau keperluan..."
                        class="block w-full rounded-lg border border-[#E5E7EB] pl-10 pr-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if(count($managedDepts) > 1 || auth()->user()->isAdmin() || auth()->user()->isGa())
                        <select name="department" class="rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm">
                            <option value="">Semua Departemen</option>
                            @foreach($managedDepts as $dept)
                                <option value="{{ $dept }}" {{ request('department') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                            @endforeach
                        </select>
                    @endif

                    <select name="status" class="rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm">
                        <option value="all">Semua Status Riwayat</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Kembali</option>
                        <option value="closed_manually" {{ request('status') === 'closed_manually' ? 'selected' : '' }}>Tutup Manual</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Dicabut (Revoked)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                    </select>

                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition-colors">
                        Filter
                    </button>

                    @if(request()->filled('search') || request()->filled('department') || (request()->filled('status') && request('status') !== 'all'))
                        <a href="{{ route('sik.history.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs font-semibold text-[#475569] uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-4">Nomor & Pemohon</th>
                            <th scope="col" class="px-4 py-4">Departemen</th>
                            <th scope="col" class="px-4 py-4">Waktu Keluar</th>
                            <th scope="col" class="px-4 py-4">Estimasi Kembali</th>
                            <th scope="col" class="px-4 py-4">Realisasi Kembali</th>
                            <th scope="col" class="px-4 py-4 text-center">Durasi Faktual</th>
                            <th scope="col" class="px-4 py-4 text-center">Keterlambatan</th>
                            <th scope="col" class="px-4 py-4 text-center">Status Akhir</th>
                            <th scope="col" class="px-5 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white">
                        @forelse ($historyPermits as $permit)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold text-[#0F172A] font-mono text-xs">{{ $permit->sik_number }}</span>
                                    <div class="font-semibold text-sm text-slate-900 mt-0.5">{{ $permit->user->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $permit->permit_date->format('d M Y') }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs text-slate-700">
                                    <span class="font-medium">{{ $permit->department }}</span>
                                    <div class="text-[11px] text-slate-400 capitalize">{{ $permit->purpose_type }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs">
                                    @if($permit->actual_departure_at)
                                        <div class="font-bold text-slate-900 font-mono">{{ $permit->actual_departure_at->format('H:i') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $permit->actual_departure_at->format('d/m/Y') }}</div>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs font-mono">
                                    <div class="text-slate-900">{{ $permit->expected_return_time }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $permit->permit_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-xs">
                                    @if($permit->actual_return_at)
                                        <div class="font-bold text-slate-900 font-mono">{{ $permit->actual_return_at->format('H:i') }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $permit->actual_return_at->format('d/m/Y') }}</div>
                                    @elseif($permit->status === 'closed_manually')
                                        <span class="text-amber-700 text-xs">Tutup Manual</span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-xs font-mono font-semibold text-slate-800">
                                    {{ $permit->actual_duration_formatted }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-xs">
                                    @if($permit->is_late)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 font-mono">
                                            +{{ $permit->late_duration_formatted }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-xs font-mono">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($permit->status === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            SUDAH KEMBALI
                                        </span>
                                    @elseif($permit->status === 'closed_manually')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-300">
                                            TUTUP MANUAL
                                        </span>
                                    @elseif($permit->status === 'revoked')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            REVOKED
                                        </span>
                                    @elseif($permit->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                            REJECTED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs font-medium">
                                    <a href="{{ route('sik.show', $permit) }}" class="inline-flex items-center text-[#2563EB] hover:text-[#1D4ED8] font-semibold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-slate-400 italic">
                                    Belum ada data riwayat SIK departemen yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historyPermits->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $historyPermits->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
