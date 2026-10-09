@extends('layouts.app')

@section('title', 'Dashboard Kendaraan - SBA')

@section('content')
<div class="space-y-8 pb-12">
    <!-- Header with Standard Period Filter -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-[11px] font-bold tracking-wider uppercase rounded-md bg-blue-50 text-[#2563EB] border border-blue-200">
                    Operational Fleet Panel
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs text-slate-500 font-medium">Manajemen Kendaraan</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Dashboard Operasional Kendaraan</h1>
            <p class="text-sm text-slate-500">Monitoring ketersediaan armada, penugasan supir, permohonan kendaraan, dan konsumsi BBM.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-dashboard-period-filter 
                :action="route('vehicles.dashboard')"
                :period="$periodInfo['period']"
                :dateInput="$periodInfo['date_input']"
                :startDate="$periodInfo['start_date']"
                :endDate="$periodInfo['end_date']"
                formId="vehiclePeriodFilterForm"
                jsPrefix="vehicle"
            />
            <div class="flex items-center gap-2">
                <a href="{{ route('vehicles.requests.index') }}" class="px-3.5 py-2 bg-[#2563EB] text-white rounded-xl text-xs font-semibold hover:bg-blue-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">assignment</span>
                    Permohonan
                </a>
                <a href="{{ route('vehicles.fuel.index') }}" class="px-3.5 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">local_gas_station</span>
                    BBM
                </a>
            </div>
        </div>
    </div>

    <!-- Section A: Kesiapan Armada & Supir (Real-Time Operational Availability) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Armada Snapshot -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">directions_car</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-slate-900">Status Kesiapan Armada</h2>
                        <p class="text-xs text-slate-500">Ketersediaan mobil operasional pabrik</p>
                    </div>
                </div>
                <span class="text-xs font-extrabold text-[#2563EB] bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200 shrink-0 self-start sm:self-auto" style="border: 1px solid #BFDBFE;">
                    {{ $totalVehicles }} Total Unit
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200" style="border: 1px solid #A7F3D0; background-color: #F0FDF4;">
                    <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Siap Pakai</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $availableVehicles }}</p>
                    <p class="text-[10px] text-emerald-700/70 mt-0.5">Available</p>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200" style="border: 1px solid #DDD6FE; background-color: #FAF5FF;">
                    <p class="text-[10px] font-bold text-purple-800 uppercase tracking-wider">Sedang Dipakai</p>
                    <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ $inUseVehiclesCount }}</p>
                    <p class="text-[10px] text-purple-700/70 mt-0.5">In-Use Saat Ini</p>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200" style="border: 1px solid #FDE68A; background-color: #FFFBEB;">
                    <p class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">Perbaikan</p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $maintenanceVehicles }}</p>
                    <p class="text-[10px] text-amber-700/70 mt-0.5">Maintenance</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200" style="border: 1px solid #E2E8F0; background-color: #F8FAFC;">
                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Non-Aktif</p>
                    <p class="text-2xl font-extrabold text-slate-700 mt-1">{{ $inactiveVehicles }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Inactive</p>
                </div>
            </div>
        </div>

        <!-- Supir Snapshot -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">person</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-slate-900">Status Kesiapan Supir</h2>
                        <p class="text-xs text-slate-500">Ketersediaan personil supir operasional</p>
                    </div>
                </div>
                <span class="text-xs font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 shrink-0 self-start sm:self-auto" style="border: 1px solid #C7D2FE;">
                    {{ $totalDrivers }} Total Supir
                </span>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200" style="border: 1px solid #A7F3D0; background-color: #F0FDF4;">
                    <p class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Siap Tugas</p>
                    <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $availableDrivers }}</p>
                    <p class="text-[10px] text-emerald-700/70 mt-0.5">Available</p>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200" style="border: 1px solid #DDD6FE; background-color: #FAF5FF;">
                    <p class="text-[10px] font-bold text-purple-800 uppercase tracking-wider">Sedang Bertugas</p>
                    <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ $onDutyDriversCount }}</p>
                    <p class="text-[10px] text-purple-700/70 mt-0.5">On Duty Saat Ini</p>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200" style="border: 1px solid #E2E8F0; background-color: #F8FAFC;">
                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Non-Aktif</p>
                    <p class="text-2xl font-extrabold text-slate-700 mt-1">{{ $inactiveDrivers }}</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Inactive</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section B: Statistik Permohonan dalam Periode Aktif -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2">
            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-blue-600">analytics</span>
                Statistik Permohonan Kendaraan (Periode Aktif)
            </h2>
            <span class="text-xs text-slate-500 font-medium">Berdasarkan jadwal permohonan yang diajukan</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500 truncate" title="Total Permohonan">Total Permohonan</span>
                    <span class="p-2 bg-slate-100 text-slate-600 rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    </span>
                </div>
                <div class="mt-3 min-w-0">
                    <div class="text-2xl font-bold text-slate-900 truncate">{{ number_format($totalRequestsInPeriod) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5 truncate">Dalam Periode</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-amber-50/20 min-w-0" style="border: 1px solid #FDE68A;">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-amber-700 truncate" title="Menunggu GA">Menunggu GA</span>
                    <span class="p-2 bg-amber-100 text-amber-600 rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                    </span>
                </div>
                <div class="mt-3 min-w-0">
                    <div class="text-2xl font-bold text-amber-600 truncate">{{ number_format($pendingRequestsInPeriod) }}</div>
                    <div class="text-[11px] text-amber-700/70 mt-0.5 truncate" title="Dalam Periode ({{ $totalPendingBacklog }} Total Antrean)">Dalam Periode ({{ $totalPendingBacklog }} Total Antrean)</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-blue-50/20 min-w-0" style="border: 1px solid #BFDBFE;">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-blue-700 truncate" title="Disetujui / Berjalan">Disetujui / Berjalan</span>
                    <span class="p-2 bg-blue-100 text-blue-600 rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-[18px]">task_alt</span>
                    </span>
                </div>
                <div class="mt-3 min-w-0">
                    <div class="text-2xl font-bold text-[#2563EB] truncate">{{ number_format($approvedInPeriod) }}</div>
                    <div class="text-[11px] text-blue-700/70 mt-0.5 truncate">Jadwal Disetujui Periode Ini</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl shadow-sm flex flex-col justify-between bg-emerald-50/20 min-w-0" style="border: 1px solid #A7F3D0;">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-emerald-700 truncate" title="Perjalanan Selesai">Perjalanan Selesai</span>
                    <span class="p-2 bg-emerald-100 text-emerald-600 rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-[18px]">done_all</span>
                    </span>
                </div>
                <div class="mt-3 min-w-0">
                    <div class="text-2xl font-bold text-emerald-600 truncate">{{ number_format($completedInPeriod) }}</div>
                    <div class="text-[11px] text-emerald-700/70 mt-0.5 truncate">Completed</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between min-w-0 col-span-2 sm:col-span-1 md:col-span-1 xl:col-span-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-slate-500 truncate" title="Ditolak / Batal">Ditolak / Batal</span>
                    <span class="p-2 bg-slate-100 text-slate-600 rounded-xl shrink-0">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                    </span>
                </div>
                <div class="mt-3 min-w-0">
                    <div class="text-2xl font-bold text-slate-700 truncate">{{ number_format($rejectedInPeriod + $cancelledInPeriod) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5 truncate" title="{{ $rejectedInPeriod }} Ditolak, {{ $cancelledInPeriod }} Batal">{{ $rejectedInPeriod }} Ditolak, {{ $cancelledInPeriod }} Batal</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section C: Konsumsi BBM (Periode Aktif) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[24px]">local_gas_station</span>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-slate-900">Konsumsi Bahan Bakar (Periode Aktif)</h2>
                    <p class="text-xs text-slate-500">Total volume liter, pengeluaran biaya BBM, dan efisiensi rata-rata</p>
                </div>
            </div>
            <a href="{{ route('vehicles.fuel.index') }}" class="text-xs font-bold text-[#2563EB] hover:underline flex items-center gap-1 shrink-0 self-end sm:self-auto">
                Detail Log BBM
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Volume BBM</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalFuelLiters, 2, ',', '.') }} <span class="text-sm font-semibold text-slate-500">Liter</span></h3>
                </div>
                <span class="p-3 bg-blue-100 text-blue-700 rounded-xl">
                    <span class="material-symbols-outlined text-[22px]">water_drop</span>
                </span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Biaya Pengisian</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($totalFuelCost, 0, ',', '.') }}</h3>
                </div>
                <span class="p-3 bg-emerald-100 text-emerald-700 rounded-xl">
                    <span class="material-symbols-outlined text-[22px]">payments</span>
                </span>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Efisiensi</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-1">
                        {{ $avgKmLiter !== null ? number_format($avgKmLiter, 2, ',', '.') : 'N/A' }} 
                        <span class="text-sm font-semibold text-slate-500">km/L</span>
                    </h3>
                </div>
                <span class="p-3 bg-purple-100 text-purple-700 rounded-xl">
                    <span class="material-symbols-outlined text-[22px]">speed</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Section D: Kendaraan Sedang Digunakan & Jadwal Terdekat -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sedang Digunakan Saat Ini -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
                    <h2 class="text-base font-bold text-slate-900">Armada Sedang Digunakan Saat Ini</h2>
                </div>
                <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-200 shrink-0 self-start sm:self-auto">
                    {{ $activeTrips->count() }} Sedang Jalan
                </span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($activeTrips as $trip)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-blue-600">{{ $trip->request_number }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="font-bold text-slate-800 truncate">{{ $trip->vehicle ? $trip->vehicle->name . ' (' . $trip->vehicle->plate_number . ')' : 'Armada Belum Terpasang' }}</span>
                            </div>
                            <p class="text-slate-600">
                                <span class="font-semibold">Pemohon:</span> {{ $trip->user->name ?? '-' }} | 
                                <span class="font-semibold">Supir:</span> {{ $trip->driver ? $trip->driver->name : 'Lepas Kunci' }}
                            </p>
                            <p class="text-slate-500 truncate"><span class="font-semibold">Tujuan:</span> {{ $trip->destination }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                Sampai {{ $trip->end_datetime->format('H:i') }}
                            </span>
                            <p class="text-[10px] text-slate-400 mt-1">{{ $trip->end_datetime->format('d M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 italic">
                        Tidak ada armada yang sedang berada dalam perjalanan saat ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Jadwal Penugasan Mendatang -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-blue-600">event</span>
                    <h2 class="text-base font-bold text-slate-900">Jadwal Penggunaan Mendatang</h2>
                </div>
                <span class="text-xs font-medium text-slate-500 shrink-0 self-start sm:self-auto">Dalam periode</span>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($upcomingTrips as $trip)
                    <div class="py-3 flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-blue-600">{{ $trip->request_number }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="font-bold text-slate-800 truncate">{{ $trip->vehicle ? $trip->vehicle->name : 'Mobil Terpasang' }}</span>
                            </div>
                            <p class="text-slate-600 truncate">{{ $trip->user->name ?? '-' }} — Ke {{ $trip->destination }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                {{ $trip->start_datetime->format('d M H:i') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 italic">
                        Belum ada jadwal permohonan disetujui untuk waktu mendatang.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
