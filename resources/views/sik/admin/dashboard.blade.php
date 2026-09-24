@extends('layouts.app')

@section('title', 'Dashboard SIK — Administrator')

@section('content')
<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-blue-50 text-[#2563EB] border border-blue-200">
                    Administrative Panel
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Surat Izin Keluar (SIK)</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Dashboard Administrasi SIK</h1>
            <p class="text-sm text-slate-500">Ringkasan operasional izin keluar karyawan, status gerbang pabrik, dan pengawasan departemen.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <x-dashboard-period-filter 
                :action="route('admin.sik.dashboard')"
                :period="$periodInfo['period']"
                :dateInput="$periodInfo['date_input']"
                :startDate="$periodInfo['start_date']"
                :endDate="$periodInfo['end_date']"
                formId="sikPeriodFilterForm"
                jsPrefix="sik"
            />
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.sik.index') }}" class="px-3.5 py-2 bg-[#2563EB] text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">list_alt</span>
                    Semua SIK
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.settings.department-approvals.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm" title="Pengaturan Approval">
                    <span class="material-symbols-outlined text-[16px]">tune</span>
                    Approval
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Total Permits -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Total SIK</span>
                <span class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">assignment</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalPermits) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Dalam Periode</div>
            </div>
        </div>

        <!-- Pending Approval -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-amber-50/20" style="border: 1px solid #FDE68A;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-amber-700">Menunggu</span>
                <span class="p-2 bg-amber-100 text-amber-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-amber-600">{{ number_format($pendingPermits) }}</div>
                <div class="text-[11px] text-amber-700/70 mt-0.5">Dalam Periode ({{ $totalPendingBacklog }} Total Antrean)</div>
            </div>
        </div>

        <!-- Active Outside -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-purple-50/20" style="border: 1px solid #E9D5FF;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-purple-700">Di Luar Pabrik</span>
                <span class="p-2 bg-purple-100 text-purple-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">directions_walk</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($activeOutside) }}</div>
                <div class="text-[11px] text-purple-700/70 mt-0.5">Dalam Periode</div>
            </div>
        </div>

        <!-- Overdue -->
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

        <!-- Returned -->
        <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-emerald-50/20" style="border: 1px solid #A7F3D0;">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-emerald-700">Selesai Kembali</span>
                <span class="p-2 bg-emerald-100 text-emerald-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-emerald-600">{{ number_format($returnedToday) }}</div>
                <div class="text-[11px] text-emerald-700/70 mt-0.5">Tercatat di Gate</div>
            </div>
        </div>

        <!-- Rejected / Revoked / Closed -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">Ditolak / Arsip</span>
                <span class="p-2 bg-slate-100 text-slate-600 rounded-xl">
                    <span class="material-symbols-outlined text-[18px]">folder_off</span>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold text-slate-700">{{ number_format($totalRejected + $totalRevoked + $totalClosedManually) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">Tidak Terealisasi</div>
            </div>
        </div>
    </div>

    <!-- Breakdown Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Department Breakdown Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Sebaran SIK per Departemen</h2>
                    <p class="text-xs text-slate-500">Pengawasan izin keluar aktif dan keterlambatan per area kerja.</p>
                </div>
                <a href="{{ route('sik.department.index') }}" class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1">
                    Monitoring Departemen
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            @if(count($deptStats) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                                <th class="pb-3">Departemen</th>
                                <th class="pb-3 text-center">Total</th>
                                <th class="pb-3 text-center">Menunggu</th>
                                <th class="pb-3 text-center">Di Luar</th>
                                <th class="pb-3 text-center">Terlambat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach($deptStats as $stat)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 font-semibold text-slate-800">{{ $stat['department'] }}</td>
                                    <td class="py-3 text-center text-slate-600 font-medium">{{ $stat['total'] }}</td>
                                    <td class="py-3 text-center">
                                        @if($stat['pending'] > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-bold text-[11px]">{{ $stat['pending'] }}</span>
                                        @else
                                            <span class="text-slate-400 font-medium">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($stat['outside'] > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-bold text-[11px]">{{ $stat['outside'] }}</span>
                                        @else
                                            <span class="text-slate-400 font-medium">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        @if($stat['overdue'] > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-bold text-[11px] animate-pulse">{{ $stat['overdue'] }}</span>
                                        @else
                                            <span class="text-slate-400 font-medium">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">Belum ada data pengajuan SIK tercatat.</div>
            @endif
        </div>

        <!-- Purpose Type & Quick Links -->
        <div class="space-y-6">
            <!-- Purpose Type Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-1">Jenis Keperluan</h2>
                <p class="text-xs text-slate-500 mb-4">Distribusi keperluan dinas vs pribadi.</p>

                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                                Keperluan Dinas
                            </span>
                            <span>{{ $dinasCount }} ({{ $totalPermits > 0 ? round(($dinasCount / $totalPermits) * 100) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-[#2563EB] h-2 rounded-full" style="width: {{ $totalPermits > 0 ? ($dinasCount / $totalPermits) * 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-700 mb-1">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-slate-600 inline-block"></span>
                                Keperluan Pribadi
                            </span>
                            <span>{{ $pribadiCount }} ({{ $totalPermits > 0 ? round(($pribadiCount / $totalPermits) * 100) : 0 }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-slate-600 h-2 rounded-full" style="width: {{ $totalPermits > 0 ? ($pribadiCount / $totalPermits) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-900 mb-3">Tautan Cepat</h2>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('admin.sik.index') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-slate-100 transition">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[18px] text-[#2563EB]">list_alt</span>
                            Semua Daftar SIK
                        </span>
                        <span class="material-symbols-outlined text-[16px] text-slate-400">chevron_right</span>
                    </a>
                    <a href="{{ route('sik.scan-logs') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-slate-100 transition">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">history</span>
                            Riwayat Log Scan Security
                        </span>
                        <span class="material-symbols-outlined text-[16px] text-slate-400">chevron_right</span>
                    </a>
                    <a href="{{ route('admin.sik.audit-logs') }}" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 border border-slate-100 transition">
                        <span class="flex items-center gap-2 font-medium text-slate-700">
                            <span class="material-symbols-outlined text-[18px] text-purple-600">receipt_long</span>
                            Audit Log Approval & Keputusan
                        </span>
                        <span class="material-symbols-outlined text-[16px] text-slate-400">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent SIK Table -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Aktivitas SIK Terbaru</h2>
                <p class="text-xs text-slate-500">10 pengajuan surat izin keluar terakhir.</p>
            </div>
            <a href="{{ route('admin.sik.index') }}" class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1">
                Lihat Semua
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        @if($recentPermits->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                            <th class="pb-3">Nomor SIK</th>
                            <th class="pb-3">Pemohon</th>
                            <th class="pb-3">Departemen</th>
                            <th class="pb-3">Keperluan</th>
                            <th class="pb-3">Tanggal & Jam</th>
                            <th class="pb-3 text-center">Status</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentPermits as $permit)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 font-mono font-bold text-[#2563EB]">
                                    <a href="{{ route('sik.show', $permit) }}" class="hover:underline">
                                        {{ $permit->sik_number }}
                                    </a>
                                </td>
                                <td class="py-3">
                                    <div class="font-semibold text-slate-800">{{ $permit->user->name ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $permit->position ?: ($permit->user->position ?? '-') }}</div>
                                </td>
                                <td class="py-3 text-slate-600">{{ $permit->department }}</td>
                                <td class="py-3 max-w-[220px]">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $permit->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                            {{ $permit->purpose_type }}
                                        </span>
                                        <p class="font-medium text-slate-800 text-xs leading-snug line-clamp-2" title="{{ $permit->purpose }}">
                                            {{ $permit->purpose }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-3 text-slate-600 whitespace-nowrap">
                                    <div>{{ $permit->permit_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $permit->expected_departure_time }} - {{ $permit->expected_return_time }} WIB</div>
                                </td>
                                <td class="py-3 text-center whitespace-nowrap">
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
                                <td class="py-3 text-right">
                                    <a href="{{ route('sik.show', $permit) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 text-xs">Belum ada pengajuan SIK.</div>
        @endif
    </div>
</div>
@endsection
