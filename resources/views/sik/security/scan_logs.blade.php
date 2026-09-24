@extends('layouts.app')

@section('title', 'Log Riwayat Scan Security - SBA')

@section('content')
<div class="min-h-full">
    <!-- Header -->
    <header class="mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Log Riwayat Scan Pos Gerbang
                </h1>
                <p class="text-sm text-[#475569] mt-1">Catatan audit riwayat pemindaian keluar dan masuk karyawan di pos security.</p>
            </div>
            @if(auth()->user()->isSecurity())
                <div class="flex items-center gap-2">
                    <a href="{{ route('sik.security.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors shadow-sm self-start sm:self-auto">
                        <span class="material-symbols-outlined text-[18px] text-slate-500">dashboard</span>
                        Dashboard Security
                    </a>
                    <a href="{{ route('sik.scanner') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm self-start sm:self-auto"
                        style="background-color: #2563EB;">
                        <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                        Buka Scanner SIK
                    </a>
                </div>
            @endif
        </div>
    </header>

    <main>
        <!-- Filter & Search Bar -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 mb-6 shadow-sm">
            <form action="{{ route('sik.scan-logs') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan, nomor SIK, atau pos gate..."
                        class="block w-full rounded-lg border border-[#E5E7EB] pl-10 pr-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <select name="scan_type" class="rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm">
                        <option value="">Semua Jenis Scan</option>
                        <option value="EXIT" {{ request('scan_type') === 'EXIT' ? 'selected' : '' }}>Scan KELUAR (EXIT)</option>
                        <option value="RETURN" {{ request('scan_type') === 'RETURN' ? 'selected' : '' }}>Scan KEMBALI (RETURN)</option>
                    </select>

                    <input type="date" name="date" value="{{ request('date') }}"
                        class="rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm">

                    <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-900 transition-colors">
                        Filter
                    </button>

                    @if(request()->filled('search') || request()->filled('scan_type') || request()->filled('date'))
                        <a href="{{ route('sik.scan-logs') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Logs Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left text-sm">
                    <thead class="bg-[#F8FAFC] text-xs font-semibold text-[#475569] uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4">Waktu Scan</th>
                            <th scope="col" class="px-6 py-4">Jenis Scan</th>
                            <th scope="col" class="px-6 py-4">Nomor SIK & Karyawan</th>
                            <th scope="col" class="px-6 py-4">Departemen</th>
                            <th scope="col" class="px-6 py-4">Pos Gerbang / Petugas</th>
                            <th scope="col" class="px-6 py-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] bg-white">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $log->scanned_at->format('d M Y') }}</div>
                                    <div class="text-xs font-mono text-slate-500">{{ $log->scanned_at->format('H:i:s') }} WIB</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->scan_type === 'EXIT')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                            KELUAR (EXIT)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            KEMBALI (RETURN)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-blue-600 font-mono text-xs">{{ $log->exitPermit ? $log->exitPermit->sik_number : '-' }}</span>
                                    <div class="font-semibold text-sm text-slate-900 mt-0.5">{{ $log->exitPermit && $log->exitPermit->user ? $log->exitPermit->user->name : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-700">
                                    {{ $log->exitPermit ? $log->exitPermit->department : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800 text-xs">{{ $log->gate ?: 'Pos Gerbang' }}</div>
                                    <div class="text-[11px] text-slate-500">Security: {{ $log->security ? $log->security->name : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-600">
                                    {{ $log->notes ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                                    Belum ada catatan log pemindaian pos security.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
