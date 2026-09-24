@extends('layouts.app')

@section('title', 'Edit Varian ATK - SBA')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.items.show', $variant->item_id) }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Edit Varian ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Perbarui detail varian {{ $variant->sku }} ({{ $variant->name }}).
            </p>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
        <form action="{{ route('atk.variants.update', $variant) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Parent Item Select -->
                <div class="space-y-2 md:col-span-2">
                    <label for="item_id" class="block text-sm font-semibold text-[#0F172A]">Barang Induk <span class="text-red-500">*</span></label>
                    <select name="item_id" id="item_id" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                        @foreach($items as $itm)
                            <option value="{{ $itm->id }}" {{ old('item_id', $variant->item_id) == $itm->id ? 'selected' : '' }}>
                                {{ $itm->name }} ({{ $itm->category->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- SKU (ReadOnly) -->
                <div class="space-y-2">
                    <label for="sku_display" class="block text-sm font-semibold text-[#0F172A]">SKU (Readonly)</label>
                    <input type="text" id="sku_display" value="{{ $variant->sku }}" disabled
                        class="block w-full rounded-md border-gray-200 bg-slate-50 font-mono text-slate-500 shadow-sm sm:text-sm">
                    <p class="text-[10px] text-slate-400">SKU tidak dapat diubah setelah varian terdaftar.</p>
                </div>

                <!-- Name Input -->
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Varian <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $variant->name) }}" required placeholder="Contoh: 80 gram, Biru, Box"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                </div>

                <!-- Unit Input -->
                <div class="space-y-2">
                    <label for="unit" class="block text-sm font-semibold text-[#0F172A]">Satuan Unit <span class="text-red-500">*</span></label>
                    <input type="text" name="unit" id="unit" value="{{ old('unit', $variant->unit) }}" required placeholder="Contoh: Rim, Pcs, Pack"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                </div>

                <!-- Purchase Price Input -->
                <div class="space-y-2">
                    <label for="purchase_price" class="block text-sm font-semibold text-[#0F172A]">Harga Beli Satuan <span class="text-red-500">*</span></label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price', $variant->purchase_price) }}" min="0" required placeholder="0"
                            class="block w-full rounded-md border-gray-300 pl-10 focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                    </div>
                </div>

                <!-- Stock Input -->
                <div class="space-y-2">
                    <label for="stock" class="block text-sm font-semibold text-[#0F172A]">Stok Saat Ini <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="stock" value="{{ old('stock', $variant->stock) }}" min="0" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                </div>

                <!-- Minimum Stock Input -->
                <div class="space-y-2">
                    <label for="minimum_stock" class="block text-sm font-semibold text-[#0F172A]">Ambang Stok Minimum <span class="text-red-500">*</span></label>
                    <input type="number" name="minimum_stock" id="minimum_stock" value="{{ old('minimum_stock', $variant->minimum_stock) }}" min="0" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                </div>

                <!-- Status (is_active) Input -->
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-[#0F172A]">Status</label>
                    <div class="flex items-center gap-6 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', $variant->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                            <span class="text-sm font-medium text-slate-700">Aktif</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="0" {{ old('is_active', $variant->is_active ? '1' : '0') == '0' ? 'checked' : '' }}
                                class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                            <span class="text-sm font-medium text-slate-700">Tidak Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('atk.items.show', $variant->item_id) }}" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded text-sm font-semibold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                    Perbarui Varian
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
