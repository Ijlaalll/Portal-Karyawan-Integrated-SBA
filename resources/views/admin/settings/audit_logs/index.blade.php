@extends('layouts.app')

@section('title', 'Audit Trail (Log Aktivitas) - Pengaturan SBA')

@section('content')
<div class="min-h-full space-y-6">
    <!-- Header Section -->
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                IT Security & Audit Compliance
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#0F172A]">
                Audit Trail & Log Aktivitas
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Catatan rekam jejak pengguna: riwayat login, logout, IP address, waktu akses, serta riwayat operasional seluruh portal SBA.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.settings.audit-logs.export', request()->all()) }}" 
               data-turbo="false"
               class="inline-flex items-center gap-2 rounded-xl bg-white border border-[#CBD5E1] px-4 py-2.5 text-xs sm:text-sm font-semibold text-[#0F172A] shadow-xs hover:bg-[#F8FAFC] hover:border-[#94A3B8] transition-all">
                <span class="material-symbols-outlined text-[18px] text-[#2563EB]">download</span>
                Ekspor Log (CSV)
            </a>
        </div>
    </header>

    <!-- Metrics Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">receipt_long</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate">Total Log Terdata</p>
                <h3 class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5">{{ number_format($totalLogs) }}</h3>
            </div>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">login</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate">Login Hari Ini</p>
                <h3 class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5">{{ number_format($todayLogins) }}</h3>
            </div>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">data_saver_on</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate">Aksi Data Hari Ini</p>
                <h3 class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5">{{ number_format($todayActions) }}</h3>
            </div>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 sm:p-5 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[26px]">lan</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider truncate">IP Address Unik</p>
                <h3 class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-0.5">{{ number_format($uniqueIps) }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter and Search Bar -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl p-4 shadow-xs">
        <form action="{{ route('admin.settings.audit-logs.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3">
            <!-- Search Keyword -->
            <div class="relative flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari nama, email, IP, aktivitas..."
                       class="block w-full rounded-xl border border-[#CBD5E1] pl-10 pr-4 py-2.5 text-xs sm:text-sm text-[#0F172A] placeholder-[#94A3B8] focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-[#94A3B8] text-[20px]">search</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Period Filter -->
                <select name="period" onchange="this.form.submit()" 
                        class="rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-xs sm:text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                    <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Semua Periode</option>
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="week" {{ $period === 'week' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="month" {{ $period === 'month' ? 'selected' : '' }}>30 Hari Terakhir</option>
                </select>

                <!-- Module Filter -->
                <select name="module" onchange="this.form.submit()" 
                        class="rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-xs sm:text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                    @foreach($availableModules as $modKey => $modLabel)
                        <option value="{{ $modKey }}" {{ $module === $modKey ? 'selected' : '' }}>{{ $modLabel }}</option>
                    @endforeach
                </select>

                <!-- Action Filter -->
                <select name="action" onchange="this.form.submit()" 
                        class="rounded-xl border border-[#CBD5E1] bg-white px-3.5 py-2.5 text-xs sm:text-sm text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-2 focus:ring-blue-100 transition-all">
                    @foreach($availableActions as $actKey => $actLabel)
                        <option value="{{ $actKey }}" {{ $action === $actKey ? 'selected' : '' }}>{{ $actLabel }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-4 py-2.5 text-xs sm:text-sm font-semibold transition-colors">
                    Filter
                </button>

                @if($search || $period !== 'all' || $module !== 'all' || $action !== 'all')
                    <a href="{{ route('admin.settings.audit-logs.index') }}" 
                       class="rounded-xl border border-[#E2E8F0] hover:bg-[#F1F5F9] text-[#64748B] px-3.5 py-2.5 text-xs sm:text-sm font-semibold transition-colors"
                       title="Reset Filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white border border-[#E2E8F0] rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#F8FAFC] border-b border-[#E2E8F0] text-[#475569] font-bold text-[11px] uppercase tracking-wider">
                        <th scope="col" class="py-3.5 px-4">Waktu (WIB)</th>
                        <th scope="col" class="py-3.5 px-4">Pengguna</th>
                        <th scope="col" class="py-3.5 px-4">Modul</th>
                        <th scope="col" class="py-3.5 px-4">Aksi</th>
                        <th scope="col" class="py-3.5 px-4">Deskripsi Aktivitas</th>
                        <th scope="col" class="py-3.5 px-4">Alamat IP & Perangkat</th>
                        <th scope="col" class="py-3.5 px-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#F1F5F9]">
                    @forelse($logs as $log)
                        @php
                            $actionColors = [
                                'login'        => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'logout'       => 'bg-slate-100 text-slate-700 border-slate-300',
                                'login_failed' => 'bg-rose-50 text-rose-700 border-rose-200',
                                'create'       => 'bg-blue-50 text-blue-700 border-blue-200',
                                'update'       => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'delete'       => 'bg-red-50 text-red-700 border-red-200',
                                'approve'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'reject'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                'scan'         => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                            ];
                            $badgeColor = $actionColors[$log->action] ?? 'bg-slate-100 text-slate-700 border-slate-200';

                            $moduleLabels = [
                                'auth'              => 'Autentikasi',
                                'meeting_room'      => 'Ruang Meeting',
                                'sik'               => 'SIK',
                                'atk'               => 'ATK',
                                'vehicle'           => 'Kendaraan',
                                'toilet_inspection' => 'Inspeksi Toilet',
                                'settings'          => 'Pengaturan',
                                'global'            => 'Sistem Global',
                            ];
                            $modLabel = $moduleLabels[$log->module] ?? ucfirst($log->module);
                        @endphp
                        <tr class="hover:bg-[#F8FAFC]/80 transition-colors">
                            <!-- Timestamp -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-[#0F172A]">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-[#64748B] font-mono">{{ $log->created_at->format('H:i:s') }} WIB</div>
                                <div class="text-[10px] text-[#94A3B8]">{{ $log->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- User Info -->
                            <td class="py-3 px-4">
                                <div class="font-bold text-[#0F172A]">{{ $log->user_name ?: '-' }}</div>
                                <div class="text-xs text-[#64748B] truncate max-w-[200px]" title="{{ $log->user_email }}">
                                    {{ $log->user_email ?: '-' }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $log->user_role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $log->user_role ?: 'Guest' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Module -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $modLabel }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider border {{ $badgeColor }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>

                            <!-- Description -->
                            <td class="py-3 px-4">
                                <p class="text-xs sm:text-sm font-medium text-[#0F172A] leading-relaxed max-w-md">
                                    {{ $log->description }}
                                </p>
                            </td>

                            <!-- IP Address & User Agent -->
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    <span class="material-symbols-outlined text-[14px] text-slate-500">router</span>
                                    {{ $log->ip_address ?: '-' }}
                                </div>
                                @if($log->user_agent)
                                    <div class="text-[10px] text-[#94A3B8] truncate max-w-[180px] mt-1" title="{{ $log->user_agent }}">
                                        {{ $log->user_agent }}
                                    </div>
                                @endif
                            </td>

                            <!-- Action View Detail -->
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <button type="button" 
                                        onclick="openLogDetailModal({{ json_encode($log) }})"
                                        class="p-1.5 rounded-lg text-[#64748B] hover:text-[#2563EB] hover:bg-blue-50 transition-colors"
                                        title="Lihat Detail Log">
                                    <span class="material-symbols-outlined text-[20px]">info</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-[#64748B]">
                                <div class="w-14 h-14 rounded-2xl bg-slate-50 border border-slate-200 text-[#94A3B8] flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-[28px]">search_off</span>
                                </div>
                                <h4 class="text-sm font-bold text-[#0F172A]">Tidak ada data audit trail</h4>
                                <p class="text-xs text-[#64748B] mt-1">Coba sesuaikan filter pencarian atau periode waktu Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-[#E2E8F0]">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail Audit Log -->
<div id="auditLogDetailModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[#2563EB] text-[22px]">policy</span>
                <h3 class="font-bold text-slate-800 text-base">Detail Catatan Audit Log</h3>
            </div>
            <button type="button" onclick="closeLogDetailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto custom-sidebar-scrollbar text-xs sm:text-sm">
            <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider block">ID Log</span>
                    <span id="modal-log-id" class="font-bold font-mono text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider block">Waktu Akses</span>
                    <span id="modal-log-time" class="font-bold text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider block">Pengguna</span>
                    <span id="modal-log-user" class="font-bold text-slate-800">-</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 font-semibold uppercase tracking-wider block">Alamat IP</span>
                    <span id="modal-log-ip" class="font-bold font-mono text-slate-800">-</span>
                </div>
            </div>

            <div>
                <span class="text-xs font-bold text-slate-700 block mb-1">Deskripsi Aktivitas</span>
                <p id="modal-log-desc" class="p-3 bg-white border border-slate-200 rounded-xl text-slate-800 font-medium leading-relaxed"></p>
            </div>

            <div>
                <span class="text-xs font-bold text-slate-700 block mb-1">Perangkat / User Agent</span>
                <p id="modal-log-ua" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 font-mono text-[11px] break-all"></p>
            </div>

            <div id="modal-log-details-section">
                <span class="text-xs font-bold text-slate-700 block mb-1">Payload Metadata (JSON)</span>
                <pre id="modal-log-details" class="p-3 bg-slate-900 text-emerald-400 font-mono text-[11px] rounded-xl overflow-x-auto max-h-48"></pre>
            </div>
        </div>

        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeLogDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openLogDetailModal(log) {
        document.getElementById('modal-log-id').textContent = '#' + log.id;
        document.getElementById('modal-log-time').textContent = new Date(log.created_at).toLocaleString('id-ID');
        document.getElementById('modal-log-user').textContent = (log.user_name || '-') + ' (' + (log.user_email || '-') + ')';
        document.getElementById('modal-log-ip').textContent = log.ip_address || '-';
        document.getElementById('modal-log-desc').textContent = log.description || '-';
        document.getElementById('modal-log-ua').textContent = log.user_agent || '-';

        const detailsSection = document.getElementById('modal-log-details-section');
        const detailsEl = document.getElementById('modal-log-details');
        if (log.details && Object.keys(log.details).length > 0) {
            detailsEl.textContent = JSON.stringify(log.details, null, 2);
            detailsSection.classList.remove('hidden');
        } else {
            detailsSection.classList.add('hidden');
        }

        document.getElementById('auditLogDetailModal').classList.remove('hidden');
    }

    function closeLogDetailModal() {
        document.getElementById('auditLogDetailModal').classList.add('hidden');
    }
</script>
@endsection
