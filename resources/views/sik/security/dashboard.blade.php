@extends('layouts.app')

@section('title', 'Dashboard Operasional Security — SIK SBA')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200">
                    Pos Gerbang Utama / Main Gate
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Pengawasan Keamanan SIK</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Dashboard Operasional Security</h1>
            <p class="text-sm text-slate-500">Monitoring real-time karyawan di luar area pabrik, deteksi keterlambatan gerbang, dan kontrol akses SIK.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('sik.scanner') }}" class="px-4 py-2.5 bg-[#2563EB] text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                Buka Scanner SIK
            </a>
            <a href="{{ route('sik.scan-logs') }}" class="px-4 py-2.5 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-2 shadow-sm">
                <span class="material-symbols-outlined text-[18px]">history</span>
                Riwayat SIK
            </a>
        </div>
    </div>

    <!-- 6 Metric Cards (Standardized SIK UI) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- 1. Total SIK -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total SIK</span>
                <span class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">assignment</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalPermits) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Semua Pengajuan</div>
            </div>
        </div>

        <!-- 2. Menunggu -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-amber-50/20" style="border: 1px solid #FDE68A;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-amber-700">Menunggu</span>
                <span class="p-2 bg-amber-100 text-amber-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-amber-600">{{ number_format($pendingPermits) }}</div>
                <div class="text-[11px] text-amber-700/70 mt-0.5">Menunggu Approval</div>
            </div>
        </div>

        <!-- 3. Di Luar Pabrik -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-purple-50/20" style="border: 1px solid #E9D5FF;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-purple-700">Di Luar Pabrik</span>
                <span class="p-2 bg-purple-100 text-purple-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">directions_walk</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($activeOutside) }}</div>
                <div class="text-[11px] text-purple-700/70 mt-0.5">Sedang Berada di Luar</div>
            </div>
        </div>

        <!-- 4. Terlambat -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between" style="border: 1px solid {{ $totalOverdue > 0 ? '#FDA4AF' : '#E2E8F0' }}; {{ $totalOverdue > 0 ? 'background-color: rgba(255,241,242,0.3);' : '' }}">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium {{ $totalOverdue > 0 ? 'text-rose-700 font-bold' : 'text-slate-500' }}">Terlambat</span>
                <span class="p-2 {{ $totalOverdue > 0 ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-600' }} rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">timer_off</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold {{ $totalOverdue > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format($totalOverdue) }}</div>
                <div class="text-[11px] {{ $totalOverdue > 0 ? 'text-rose-700/70' : 'text-slate-400' }} mt-0.5">Lewat Waktu Rencana</div>
            </div>
        </div>

        <!-- 5. Kembali Hari Ini -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-emerald-50/20" style="border: 1px solid #A7F3D0;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-emerald-700">Kembali Hari Ini</span>
                <span class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-emerald-600">{{ number_format($returnedToday) }}</div>
                <div class="text-[11px] text-emerald-700/70 mt-0.5">Tercatat di Gerbang</div>
            </div>
        </div>

        <!-- 6. Ditolak / Arsip -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Ditolak / Arsip</span>
                <span class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">folder_off</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-700">{{ number_format($archivedCount) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tidak Terealisasi</div>
            </div>
        </div>
    </div>

    <!-- Operational Section: Karyawan Sedang di Luar Pabrik -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h2 class="text-lg font-bold text-slate-900">Karyawan Sedang di Luar Pabrik</h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                        {{ $activeOutsidePermits->count() }} Aktif
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Daftar pemegang SIK yang telah dipindai KELUAR dan belum tercatat KEMBALI.</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400">Live Timer Server Source:</span>
                <span class="text-xs font-mono font-bold text-slate-700" id="liveServerClock">{{ \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s') }} WIB</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold text-[10px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Karyawan / Pemohon</th>
                        <th class="px-6 py-3.5">Departemen</th>
                        <th class="px-6 py-3.5">Keperluan</th>
                        <th class="px-6 py-3.5">Waktu Keluar</th>
                        <th class="px-6 py-3.5">Rencana Kembali</th>
                        <th class="px-6 py-3.5 text-center">Durasi di Luar</th>
                        <th class="px-6 py-3.5 text-center">Status Gerbang</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="outsidePermitsTableBody">
                    @forelse ($activeOutsidePermits as $permit)
                        @php
                            $deptTime = $permit->actual_departure_at ? $permit->actual_departure_at->toIso8601String() : '';
                            $expReturnTime = $permit->expected_return_datetime ? $permit->expected_return_datetime->toIso8601String() : '';
                            $isOverdue = $permit->effective_status === \App\Models\ExitPermit::STATUS_OVERDUE;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors permit-row"
                            id="row-{{ $permit->id }}"
                            data-permit-id="{{ $permit->id }}"
                            data-departure="{{ $deptTime }}"
                            data-expected-return="{{ $expReturnTime }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900 text-sm">{{ $permit->user->name ?? '-' }}</div>
                                <div class="text-slate-500 text-[11px] font-mono mt-0.5">
                                    {{ $permit->employee_id ?: ($permit->user->employee_id ?? '-') }} • {{ $permit->position ?: ($permit->user->position ?? '-') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $permit->department }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-[240px]">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $permit->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                        {{ $permit->purpose_type }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-mono">{{ $permit->sik_number }}</span>
                                </div>
                                <p class="text-slate-700 text-xs truncate" title="{{ $permit->purpose }}">{{ $permit->purpose }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">
                                <div class="text-slate-900 font-bold">
                                    {{ $permit->actual_departure_at ? $permit->actual_departure_at->format('H:i:s') : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $permit->actual_departure_at ? $permit->actual_departure_at->format('d M Y') : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono">
                                <div class="text-slate-900 font-bold">
                                    {{ $permit->expected_return_time }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $permit->permit_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex flex-col items-center">
                                    <span class="font-mono text-sm font-bold text-slate-900 timer-elapsed" id="timer-elapsed-{{ $permit->id }}">
                                        {{ $permit->actual_duration_formatted }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Berjalan</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="status-container" id="status-container-{{ $permit->id }}">
                                    @if($isOverdue)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="material-symbols-outlined text-[14px]">warning</span>
                                            TERLAMBAT
                                        </span>
                                        <div class="text-[11px] text-rose-600 font-mono font-bold mt-1 late-duration" id="late-dur-{{ $permit->id }}">
                                            +{{ $permit->late_duration_formatted }}
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                            Normal
                                        </span>
                                        <div class="text-[10px] text-slate-400 mt-1">Sesuai Rencana</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('sik.scanner') }}" class="px-3 py-1.5 bg-[#2563EB] text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1 shadow-sm">
                                        <span class="material-symbols-outlined text-[15px]">qr_code_scanner</span>
                                        Scan Kembali
                                    </a>
                                    <a href="{{ route('sik.show', $permit) }}" class="px-2.5 py-1.5 bg-slate-100 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-200 transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 italic">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">done_all</span>
                                Tidak ada karyawan yang sedang berada di luar pabrik saat ini. Seluruh izin telah kembali atau terselesaikan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Scans Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Scan Terbaru di Gerbang</h3>
                <p class="text-xs text-slate-500">Aktivitas pemindaian QR keluar dan masuk terkini oleh petugas security.</p>
            </div>
            <a href="{{ route('sik.scan-logs') }}" class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1">
                Lihat Semua Riwayat
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        @if($recentScans->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($recentScans as $scan)
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-xl {{ $scan->scan_type === 'EXIT' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                                <span class="material-symbols-outlined text-[18px]">
                                    {{ $scan->scan_type === 'EXIT' ? 'logout' : 'login' }}
                                </span>
                            </span>
                            <div>
                                <div class="font-bold text-slate-900">
                                    {{ $scan->exitPermit->user->name ?? '-' }}
                                    <span class="text-slate-400 font-normal">({{ $scan->exitPermit->department ?? '-' }})</span>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $scan->scan_type === 'EXIT' ? 'Konfirmasi KELUAR' : 'Konfirmasi KEMBALI' }} di {{ $scan->gate ?: 'Pos Utama' }}
                                    oleh <span class="font-medium text-slate-700">{{ $scan->security->name ?? 'Security' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right font-mono">
                            <div class="text-slate-900 font-semibold">{{ $scan->scanned_at->format('H:i:s') }}</div>
                            <div class="text-[10px] text-slate-400">{{ $scan->scanned_at->format('d M Y') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-slate-400 italic text-xs">
                Belum ada aktivitas pemindaian gerbang hari ini.
            </div>
        @endif
    </div>
</div>

<!-- Real-Time SIK Live Timer Engine (JavaScript) -->
<script>
    function padZero(num) {
        return num < 10 ? '0' + num : num;
    }

    function formatDuration(seconds) {
        if (seconds < 0) seconds = 0;
        const h = Math.floor(seconds / 3600);
        const m = Math.floor((seconds % 3600) / 60);
        const s = Math.floor(seconds % 60);

        if (h > 0) {
            return `${padZero(h)}:${padZero(m)}:${padZero(s)}`;
        }
        return `${padZero(m)}:${padZero(s)}`;
    }

    function updateOperationalTimers() {
        const now = new Date();
        
        // Update Live Header Clock
        const clockEl = document.getElementById('liveServerClock');
        if (clockEl) {
            const h = padZero(now.getHours());
            const m = padZero(now.getMinutes());
            const s = padZero(now.getSeconds());
            clockEl.textContent = `${h}:${m}:${s} WIB`;
        }

        const rows = document.querySelectorAll('.permit-row');
        rows.forEach(row => {
            const permitId = row.getAttribute('data-permit-id');
            const depIso = row.getAttribute('data-departure');
            const expIso = row.getAttribute('data-expected-return');

            if (!depIso) return;

            const depDate = new Date(depIso);
            const elapsedSecs = Math.floor((now - depDate) / 1000);

            // Update Elapsed Timer
            const elapsedEl = document.getElementById('timer-elapsed-' + permitId);
            if (elapsedEl) {
                elapsedEl.textContent = formatDuration(elapsedSecs);
            }

            // Check Overdue Status
            if (expIso) {
                const expDate = new Date(expIso);
                const statusContainer = document.getElementById('status-container-' + permitId);

                if (now > expDate) {
                    const lateSecs = Math.floor((now - expDate) / 1000);
                    if (statusContainer) {
                        statusContainer.innerHTML = `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="material-symbols-outlined text-[14px]">warning</span>
                                TERLAMBAT
                            </span>
                            <div class="text-[11px] text-rose-600 font-mono font-bold mt-1">
                                +${formatDuration(lateSecs)}
                            </div>
                        `;
                    }
                }
            }
        });
    }

    // Run immediately on page load and tick every 1000ms
    document.addEventListener('turbo:load', function() {
        updateOperationalTimers();
    });
    document.addEventListener('DOMContentLoaded', function() {
        updateOperationalTimers();
    });

    setInterval(updateOperationalTimers, 1000);
</script>
@endsection
