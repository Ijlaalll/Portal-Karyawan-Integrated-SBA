@extends('layouts.app')

@section('title', 'Dashboard ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 border-b border-[#E5E7EB] pb-6">
        @if(auth()->user()->isAdmin())
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Dashboard Monitoring ATK
                </h1>
                <p class="text-sm text-[#475569] mt-1">
                    Pantau stok barang, log mutasi, dan statistik permintaan alat tulis kantor.
                </p>
            </div>
        @else
            <div>
                <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Dashboard Manajemen ATK
                </h1>
                <p class="text-sm text-[#475569] mt-1">
                    Pantau stok barang, log mutasi, dan persetujuan permintaan alat tulis kantor.
                </p>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <x-dashboard-period-filter 
                :action="route('atk.dashboard')"
                :period="$periodInfo['period']"
                :dateInput="$periodInfo['date_input']"
                :startDate="$periodInfo['start_date']"
                :endDate="$periodInfo['end_date']"
                formId="atkPeriodFilterForm"
                jsPrefix="atk"
            />
        </div>
    </header>

    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <!-- Metric Card Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Available Stock -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Stok Tersedia</span>
                <p class="text-3xl font-bold text-[#0F172A]">{{ $availableStock }}</p>
                <p class="text-[11px] text-slate-500 font-medium">Dari {{ $totalVariants }} varian aktif</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">inventory</span>
            </div>
        </div>

        <!-- Card 2: Low Stock -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Stok Menipis</span>
                <p class="text-3xl font-bold text-amber-600">{{ $lowStockCount }}</p>
                <p class="text-[11px] text-amber-500 font-semibold">Perlu restock segera</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">warning</span>
            </div>
        </div>

        <!-- Card 3: Out of Stock -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Stok Habis</span>
                <p class="text-3xl font-bold text-red-600">{{ $outOfStockCount }}</p>
                <p class="text-[11px] text-red-500 font-semibold">Stok kosong</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">production_quantity_limits</span>
            </div>
        </div>

        <!-- Card 4: Request in Period -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all">
            <div class="space-y-2">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Request Periode Ini</span>
                <p class="text-3xl font-bold text-indigo-600">{{ $totalRequestsInPeriod }}</p>
                <p class="text-[11px] text-indigo-500 font-semibold">{{ $pendingCount }} Menunggu ({{ $totalPendingBacklog }} Total)</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-[28px]">pending_actions</span>
            </div>
        </div>
    </div>

    <!-- Request Status Breakdown -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4">
        <div class="text-center p-2.5 bg-white rounded-lg border border-slate-100 shadow-2xs min-w-0">
            <p class="text-xs font-medium text-slate-500 truncate" title="Menunggu">Menunggu</p>
            <p class="text-lg font-bold text-indigo-700 mt-1 truncate">{{ $pendingCount }}</p>
        </div>
        <div class="text-center p-2.5 bg-white rounded-lg border border-slate-100 shadow-2xs min-w-0">
            <p class="text-xs font-medium text-slate-500 truncate" title="Disetujui">Disetujui</p>
            <p class="text-lg font-bold text-emerald-700 mt-1 truncate">{{ $approvedCount }}</p>
        </div>
        <div class="text-center p-2.5 bg-white rounded-lg border border-slate-100 shadow-2xs min-w-0">
            <p class="text-xs font-medium text-slate-500 truncate" title="Sedang Diproses">Sedang Diproses</p>
            <p class="text-lg font-bold text-blue-700 mt-1 truncate">{{ $processingCount }}</p>
        </div>
        <div class="text-center p-2.5 bg-white rounded-lg border border-slate-100 shadow-2xs min-w-0">
            <p class="text-xs font-medium text-slate-500 truncate" title="Selesai">Selesai</p>
            <p class="text-lg font-bold text-slate-700 mt-1 truncate">{{ $completedCount }}</p>
        </div>
        <div class="text-center p-2.5 bg-white rounded-lg border border-slate-100 shadow-2xs min-w-0 col-span-2 sm:col-span-1 md:col-span-1 xl:col-span-1">
            <p class="text-xs font-medium text-slate-500 truncate" title="Ditolak">Ditolak</p>
            <p class="text-lg font-bold text-rose-600 mt-1 truncate">{{ $rejectedCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Requests Section -->
        <div class="lg:col-span-2 bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Permintaan Terbaru (Periode Aktif)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#475569]">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase">
                            <th class="py-3 px-4">No. Request</th>
                            <th class="py-3 px-4">Pemohon</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse($recentRequests as $request)
                            <tr>
                                <td class="py-3 px-4 font-semibold text-[#2563EB]">
                                    <a href="{{ route('atk.requests.show', $request->id) }}" class="hover:underline">
                                        {{ $request->request_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4">{{ $request->user->name }}</td>
                                <td class="py-3 px-4">
                                    @php
                                        $statuses = [
                                            'diajukan' => ['class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'label' => 'Diajukan'],
                                            'disetujui' => ['class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'label' => 'Disetujui'],
                                            'diproses' => ['class' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'label' => 'Diproses'],
                                            'selesai' => ['class' => 'bg-slate-50 text-slate-700 ring-slate-600/10', 'label' => 'Selesai'],
                                            'ditolak' => ['class' => 'bg-red-50 text-red-700 ring-red-600/20', 'label' => 'Ditolak'],
                                            'dibatalkan' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Batal'],
                                        ];
                                        $st = $statuses[$request->status] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($request->status)];
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $st['class'] }} ring-1 ring-inset">
                                        {{ $st['label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('atk.requests.show', $request->id) }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors">
                                        {{ auth()->user()->isGa() ? 'Tinjau' : 'Detail' }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center italic text-slate-400">Tidak ada permintaan aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alerts / Sidebar Panels -->
        <div class="space-y-6">
            <!-- Low Stock Alerts Panel -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2 flex items-center gap-2 text-amber-600">
                    <span class="material-symbols-outlined text-[20px]">warning</span> Stok Menipis
                </h2>
                <div class="divide-y divide-[#E5E7EB] text-sm">
                    @forelse($lowStockVariants as $variant)
                        <div class="py-2.5 flex items-center justify-between">
                            <div>
                                <p class="font-bold text-[#0F172A]">{{ $variant->item->name }}</p>
                                <p class="text-xs text-slate-500">{{ $variant->name }} ({{ $variant->sku }})</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-amber-600">{{ $variant->stock }} {{ $variant->unit }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold">Min: {{ $variant->minimum_stock }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center italic text-slate-400">Semua stok varian mencukupi.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Stock Movements Panel -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Log Mutasi Terakhir (Periode Aktif)</h2>
                <div class="divide-y divide-[#E5E7EB] text-xs space-y-3">
                    @forelse($recentMovements as $mov)
                        <div class="py-2 flex justify-between items-start gap-2">
                            <div class="space-y-0.5">
                                <p class="font-bold text-slate-800">{{ $mov->variant->item->name }} - {{ $mov->variant->name }}</p>
                                <p class="text-[10px] text-slate-500">Oleh: {{ $mov->actor->name }}</p>
                                <p class="text-[10px] text-slate-400 italic">"{{ $mov->reason ?: '-' }}"</p>
                            </div>
                            <div class="text-right shrink-0">
                                @if($mov->quantity > 0)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">+{{ $mov->quantity }}</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">{{ $mov->quantity }}</span>
                                @endif
                                <p class="text-[9px] text-slate-400 mt-1">{{ $mov->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center italic text-slate-400">Belum ada aktivitas mutasi stok.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
