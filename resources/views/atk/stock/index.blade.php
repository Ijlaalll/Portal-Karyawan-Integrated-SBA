@extends('layouts.app')

@section('title', 'Stok & Log ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Penyesuaian Stok ATK
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Lakukan pencocokan stok fisik (Stock Opname) dan tinjau riwayat mutasi stok.
            </p>
        </div>
        <a href="{{ route('atk.stock.history') }}" class="bg-white border border-[#E5E7EB] hover:bg-slate-50 text-[#0F172A] rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">history</span> Riwayat Mutasi Stok
        </a>
    </header>

    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search bar & actions -->
    <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-sm flex items-center gap-4 justify-between">
        <form action="{{ route('atk.stock.index') }}" method="GET" class="w-full max-w-md flex items-center gap-2">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari varian, SKU, atau barang induk..."
                    class="block w-full rounded-lg bg-white pl-9 pr-3 py-2 text-xs text-slate-900 focus:outline-none transition-colors"
                    style="border: 1px solid #CBD5E1;">
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-lg text-xs font-semibold shadow-sm transition-colors cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('atk.stock.index') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <!-- Variants Stock Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">SKU</th>
                        <th class="py-3.5 px-6">Varian</th>
                        <th class="py-3.5 px-6">Barang Induk</th>
                        <th class="py-3.5 px-6">Stok Sistem</th>
                        <th class="py-3.5 px-6">Ambang Batas</th>
                        <th class="py-3.5 px-6">Ketersediaan</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($variants as $variant)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 font-mono text-xs font-semibold text-slate-600 bg-slate-50 border-r border-slate-100">{{ $variant->sku }}</td>
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">{{ $variant->name }}</td>
                            <td class="py-4 px-6">
                                <p class="font-medium text-slate-800">{{ $variant->item->name }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold uppercase">{{ $variant->item->category->name }}</p>
                            </td>
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">{{ $variant->stock }} {{ $variant->unit }}</td>
                            <td class="py-4 px-6 text-slate-500">{{ $variant->minimum_stock }} {{ $variant->unit }}</td>
                            <td class="py-4 px-6">
                                @php
                                    $availability = $variant->availability_status;
                                    $tags = [
                                        'Tersedia' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'Stok Menipis' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        'Habis' => 'bg-red-50 text-red-700 ring-red-600/20'
                                    ];
                                    $tagClass = $tags[$availability] ?? 'bg-slate-50 text-slate-600';
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $tagClass }} ring-1 ring-inset">
                                    {{ $availability }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="button" 
                                    onclick="openAdjustModal('{{ $variant->id }}', '{{ $variant->sku }}', '{{ addslashes($variant->item->name . ' - ' . $variant->name) }}', '{{ $variant->stock }}', '{{ $variant->unit }}')"
                                    class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-3 py-2 rounded transition-colors inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">tune</span> Opname / Sesuaikan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center italic text-slate-400">Tidak ada varian yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variants->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $variants->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="adjust-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#0F172A]">Stock Opname / Penyesuaian</h3>
            <button type="button" onclick="closeAdjustModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>
        </div>

        <form id="adjust-form" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="variant_id" id="adjust-variant-id">

            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Varian</p>
                <p class="text-base font-bold text-[#0F172A] mt-0.5" id="modal-sku-name"></p>
            </div>

            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                <div>
                    <span class="text-xs text-slate-500 font-medium">Stok Sistem</span>
                    <p class="text-lg font-bold text-slate-800 mt-0.5" id="modal-current-stock"></p>
                </div>
                <div>
                    <span class="text-xs text-slate-500 font-medium">Selisih Hitung</span>
                    <p class="text-lg font-bold text-slate-800 mt-0.5" id="modal-difference">0</p>
                </div>
            </div>

            <div class="space-y-2">
                <label for="modal-physical-stock" class="block text-sm font-semibold text-[#0F172A]">Stok Fisik Aktual <span class="text-red-500">*</span></label>
                <input type="number" name="physical_stock" id="modal-physical-stock" required min="0" oninput="calculateDifference()"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm" placeholder="Masukkan jumlah fisik saat ini">
            </div>

            <div class="space-y-2">
                <label for="modal-reason" class="block text-sm font-semibold text-[#0F172A]">Alasan Penyesuaian <span class="text-red-500">*</span></label>
                <textarea name="reason" id="modal-reason" required rows="3" placeholder="Contoh: Selisih stock opname bulanan atau barang rusak."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded text-sm font-semibold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                    Simpan Penyesuaian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentStockVal = 0;
    let unitVal = '';

    function openAdjustModal(variantId, sku, name, currentStock, unit) {
        currentStockVal = parseInt(currentStock);
        unitVal = unit;

        document.getElementById('adjust-variant-id').value = variantId;
        document.getElementById('adjust-form').action = `/atk/stock/${variantId}/adjust`;
        document.getElementById('modal-sku-name').textContent = `${sku} - ${name}`;
        document.getElementById('modal-current-stock').textContent = `${currentStock} ${unit}`;
        document.getElementById('modal-physical-stock').value = '';
        document.getElementById('modal-difference').textContent = `0 ${unit}`;
        document.getElementById('modal-difference').className = 'text-lg font-bold text-slate-800 mt-0.5';
        document.getElementById('modal-reason').value = '';
        
        document.getElementById('adjust-modal').classList.remove('hidden');
    }

    function closeAdjustModal() {
        document.getElementById('adjust-modal').classList.add('hidden');
    }

    function calculateDifference() {
        const physicalVal = parseInt(document.getElementById('modal-physical-stock').value);
        if (isNaN(physicalVal)) {
            document.getElementById('modal-difference').textContent = `0 ${unitVal}`;
            document.getElementById('modal-difference').className = 'text-lg font-bold text-slate-800 mt-0.5';
            return;
        }

        const diff = physicalVal - currentStockVal;
        const diffText = diff > 0 ? `+${diff}` : diff;
        
        const diffEl = document.getElementById('modal-difference');
        diffEl.textContent = `${diffText} ${unitVal}`;

        if (diff > 0) {
            diffEl.className = 'text-lg font-bold text-green-600 mt-0.5';
        } else if (diff < 0) {
            diffEl.className = 'text-lg font-bold text-red-600 mt-0.5';
        } else {
            diffEl.className = 'text-lg font-bold text-slate-800 mt-0.5';
        }
    }
</script>
@endsection
