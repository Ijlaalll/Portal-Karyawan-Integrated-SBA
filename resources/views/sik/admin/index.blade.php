@extends('layouts.app')

@section('title', 'Semua SIK — Administrator')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sik.dashboard') }}" class="text-xs text-[#2563EB] hover:underline font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    Dashboard SIK
                </a>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Semua Surat Izin Keluar</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Daftar Semua SIK Perusahaan</h1>
            <p class="text-sm text-slate-500">Seluruh permohonan surat izin keluar dari seluruh departemen dan karyawan SBA.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.settings.department-approvals.index') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">tune</span>
                Pengaturan Approval
            </a>
        </div>
        @endif
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.sik.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari SIK</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor SIK, Nama, NIK, Keperluan..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl focus:outline-none" style="border: 1px solid #CBD5E1;">
                </div>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Departemen</label>
                <select name="department" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>DRAFT</option>
                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>MENUNGGU PERSETUJUAN</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>DISETUJUI</option>
                    <option value="outside" {{ request('status') == 'outside' ? 'selected' : '' }}>SEDANG DI LUAR</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>TERLAMBAT (OVERDUE)</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>SUDAH KEMBALI</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>DITOLAK</option>
                    <option value="revoked" {{ request('status') == 'revoked' ? 'selected' : '' }}>DICABUT</option>
                    <option value="closed_manually" {{ request('status') == 'closed_manually' ? 'selected' : '' }}>DITUTUP MANUAL</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Filter
                </button>
                <a href="{{ route('admin.sik.index') }}" class="p-2 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-xl transition" title="Reset Filter">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Table of Exit Permits -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">
                Data Surat Izin Keluar ({{ $permits->total() }} record)
            </h2>
            <div class="text-xs text-slate-400">Halaman {{ $permits->currentPage() }} dari {{ $permits->lastPage() }}</div>
        </div>

        @if($permits->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-3 px-4">Nomor SIK</th>
                            <th class="py-3 px-4">Pemohon</th>
                            <th class="py-3 px-4">Departemen</th>
                            <th class="py-3 px-4">Keperluan</th>
                            <th class="py-3 px-4">Waktu Rencana</th>
                            <th class="py-3 px-4">Waktu Aktual Gate</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach($permits as $permit)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-[#2563EB]">
                                    <a href="{{ route('sik.show', $permit) }}" class="hover:underline">
                                        {{ $permit->sik_number }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $permit->user->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">NIK: {{ $permit->employee_id ?: ($permit->user->employee_id ?? '-') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-700">{{ $permit->department }}</span>
                                    <div class="text-[10px] text-slate-400">{{ $permit->position ?: ($permit->user->position ?? '-') }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-[240px]">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $permit->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                            {{ $permit->purpose_type }}
                                        </span>
                                        <p class="font-medium text-slate-800 text-xs leading-snug line-clamp-2" title="{{ $permit->purpose }}">
                                            {{ $permit->purpose }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                    <div>{{ $permit->permit_date->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600 text-[11px]">
                                    @if($permit->actual_departure_at)
                                        <div>Out: <span class="font-semibold text-slate-800 font-mono">{{ $permit->actual_departure_at->format('H:i') }}</span></div>
                                    @else
                                        <div class="text-slate-300">Out: -</div>
                                    @endif
                                    @if($permit->actual_return_at)
                                        <div>In: <span class="font-semibold text-slate-800 font-mono">{{ $permit->actual_return_at->format('H:i') }}</span></div>
                                    @else
                                        <div class="text-slate-300">In: -</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @php $effStatus = $permit->effective_status; @endphp
                                    @if($effStatus === 'draft')
                                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px]">DRAFT</span>
                                    @elseif($effStatus === 'pending_approval')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">MENUNGGU</span>
                                    @elseif($effStatus === 'approved')
                                        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 font-bold text-[10px]">DISETUJUI</span>
                                    @elseif($effStatus === 'outside')
                                        <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px]">DI LUAR</span>
                                    @elseif($effStatus === 'overdue')
                                        <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px] animate-pulse">TERLAMBAT</span>
                                    @elseif($effStatus === 'returned')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">KEMBALI</span>
                                    @elseif($effStatus === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">DITOLAK</span>
                                    @elseif($effStatus === 'revoked')
                                        <span class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-800 font-bold text-[10px]">DICABUT</span>
                                    @elseif($effStatus === 'closed_manually')
                                        <span class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-800 font-bold text-[10px]">TUTUP MANUAL</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('sik.show', $permit) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition" title="Lihat Detail SIK">
                                            Detail
                                        </a>
                                        <a href="{{ route('sik.print', $permit) }}" target="_blank" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg transition" title="Cetak Surat Fisik">
                                            <span class="material-symbols-outlined text-[18px]">print</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $permits->links() }}
            </div>
        @else
            <div class="py-12 text-center text-slate-400 text-xs">
                Tidak ada data surat izin keluar ditemukan dengan filter saat ini.
            </div>
        @endif
    </div>
</div>
@endsection
