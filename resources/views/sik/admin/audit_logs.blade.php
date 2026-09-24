@extends('layouts.app')

@section('title', 'Audit Log SIK — Administrator')

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
                <span class="text-xs text-slate-500 font-medium">Audit Trail</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Audit Log Keputusan SIK</h1>
            <p class="text-sm text-slate-500">Catatan riwayat seluruh aksi persetujuan, penolakan, pencabutan izin, dan penutupan administratif SIK.</p>
        </div>
        <div>
            <a href="{{ route('sik.scan-logs') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                Log Scan Gerbang Security
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.sik.audit-logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Log</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor SIK, Nama Approver, Departemen, Catatan..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl focus:outline-none" style="border: 1px solid #CBD5E1;">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Aksi Status</label>
                <div class="flex gap-2">
                    <select name="status" class="flex-1 px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#2563EB] focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>DISETUJUI (APPROVED)</option>
                        <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>DITOLAK (REJECTED)</option>
                        <option value="REVOKED" {{ request('status') == 'REVOKED' ? 'selected' : '' }}>DICABUT (REVOKED)</option>
                        <option value="CLOSED_MANUALLY" {{ request('status') == 'CLOSED_MANUALLY' ? 'selected' : '' }}>DITUTUP MANUAL (CLOSED_MANUALLY)</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition">
                        Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800">
                Data Audit Log ({{ $logs->total() }} entri)
            </h2>
            <div class="text-xs text-slate-400">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</div>
        </div>

        @if($logs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider text-[10px] border-b border-slate-200">
                            <th class="py-3 px-4">Waktu Keputusan</th>
                            <th class="py-3 px-4">Nomor SIK & Pemohon</th>
                            <th class="py-3 px-4">Departemen</th>
                            <th class="py-3 px-4">Aksi Status</th>
                            <th class="py-3 px-4">Approver / Eksekutor</th>
                            <th class="py-3 px-4">Catatan / Alasan</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @foreach($logs as $log)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                    <div class="font-semibold text-slate-900">{{ $log->actioned_at->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $log->actioned_at->format('H:i:s') }} WIB</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($log->exitPermit)
                                        <a href="{{ route('sik.show', $log->exitPermit) }}" class="font-mono font-bold text-[#2563EB] hover:underline">
                                            {{ $log->exitPermit->sik_number }}
                                        </a>
                                        <div class="text-[11px] text-slate-700 font-medium">{{ $log->exitPermit->user->name ?? '-' }}</div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">
                                    {{ $log->exitPermit->department ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($log->status === 'APPROVED')
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">APPROVED</span>
                                    @elseif($log->status === 'REJECTED')
                                        <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">REJECTED</span>
                                    @elseif($log->status === 'REVOKED')
                                        <span class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-800 font-bold text-[10px]">REVOKED</span>
                                    @elseif($log->status === 'CLOSED_MANUALLY')
                                        <span class="px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px]">CLOSED_MANUALLY</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $log->approver->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $log->approver->position ?: ($log->approver->department ?? '-') }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-[280px] text-slate-600">
                                    {{ $log->note ?: '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    @if($log->exitPermit)
                                        <a href="{{ route('sik.show', $log->exitPermit) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                            Detail SIK
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @else
            <div class="py-12 text-center text-slate-400 text-xs">
                Tidak ada riwayat audit log ditemukan.
            </div>
        @endif
    </div>
</div>
@endsection
