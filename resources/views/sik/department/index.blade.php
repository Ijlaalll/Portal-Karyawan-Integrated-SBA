@extends('layouts.app')

@section('title', 'Monitoring SIK Departemen - SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Monitoring SIK Departemen
                </h1>
                <p class="text-sm text-[#475569] mt-1">Pantau seluruh status izin keluar karyawan di bawah kewenangan departemen Anda.</p>
            </div>
            <a href="{{ route('sik.history.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm self-start sm:self-auto">
                <span class="material-symbols-outlined text-[20px]">history</span>
                Lihat Arsip & Riwayat
            </a>
        </div>
    </header>

    <main>
        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-5 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[26px]">directions_walk</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Sedang di Luar">Sedang di Luar</p>
                    <p class="text-xl sm:text-2xl font-bold text-purple-700 mt-0.5">{{ $totalActiveOutside }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-5 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[26px]">alarm</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Terlambat Kembali">Terlambat Kembali</p>
                    <p class="text-xl sm:text-2xl font-bold text-rose-600 mt-0.5">{{ $totalOverdue }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-5 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[26px]">hourglass_top</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Menunggu Persetujuan">Menunggu Persetujuan</p>
                    <p class="text-xl sm:text-2xl font-bold text-amber-600 mt-0.5">{{ $totalPending }}</p>
                </div>
            </div>

            <div class="bg-white border border-[#E5E7EB] rounded-2xl p-4 sm:p-5 shadow-sm flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="p-2.5 sm:p-3 rounded-xl bg-green-50 text-green-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] sm:text-[26px]">task_alt</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-[#475569] uppercase tracking-wider truncate" title="Kembali Hari Ini">Kembali Hari Ini</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-700 mt-0.5">{{ $totalReturnedToday }}</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6 shadow-sm">
            <form action="{{ route('sik.department.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor SIK, atau keperluan..."
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
                        <option value="all">Semua Status</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="outside" {{ request('status') === 'outside' ? 'selected' : '' }}>Sedang di Luar</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Terlambat (Overdue)</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Kembali</option>
                        <option value="closed_manually" {{ request('status') === 'closed_manually' ? 'selected' : '' }}>Tutup Manual</option>
                        <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Dicabut (Revoked)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}"
                        class="rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm">

                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition-colors">
                        Filter
                    </button>

                    @if(request()->filled('search') || request()->filled('department') || (request()->filled('status') && request('status') !== 'all') || request()->filled('date'))
                        <a href="{{ route('sik.department.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- SIK Monitoring Table List -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs font-semibold text-[#475569] uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Nomor & Pemohon</th>
                            <th scope="col" class="px-6 py-4">Departemen</th>
                            <th scope="col" class="px-6 py-4">Tanggal & Rencana Waktu</th>
                            <th scope="col" class="px-6 py-4">Keperluan</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white">
                        @forelse ($permits as $permit)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-[#0F172A] font-mono text-xs">{{ $permit->sik_number }}</span>
                                    <div class="font-semibold text-sm text-slate-900 mt-0.5">{{ $permit->user->name }}</div>
                                    <div class="text-[11px] text-slate-500">ID: {{ $permit->employee_id ?: ($permit->user->employee_id ?: '-') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $permit->department }}</div>
                                    <div class="text-xs text-slate-500">{{ $permit->position ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-900">{{ $permit->permit_date->format('d M Y') }}</div>
                                    <div class="text-xs text-blue-700 font-semibold mt-0.5">
                                        {{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }} WIB
                                    </div>
                                    @if($permit->actual_departure_at)
                                        <div class="text-[10px] text-purple-700 mt-0.5">Keluar: {{ $permit->actual_departure_at->format('H:i') }} WIB</div>
                                    @endif
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
                                            DISETUJUI
                                        </span>
                                    @elseif($effStatus === 'outside')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                            SEDANG DI LUAR
                                        </span>
                                    @elseif($effStatus === 'overdue')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                                            TERLAMBAT KEMBALI
                                        </span>
                                    @elseif($effStatus === 'returned')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            SUDAH KEMBALI
                                        </span>
                                    @elseif($effStatus === 'closed_manually')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-300">
                                            TUTUP MANUAL
                                        </span>
                                    @elseif($effStatus === 'revoked')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            REVOKED
                                        </span>
                                    @elseif($effStatus === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 border border-red-200">
                                            REJECTED
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium space-x-2">
                                    <a href="{{ route('sik.show', $permit) }}" class="inline-flex items-center text-[#2563EB] hover:text-[#1D4ED8] font-semibold">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">corporate_fare</span>
                                    Tidak ada data izin keluar departemen yang cocok dengan filter.
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
