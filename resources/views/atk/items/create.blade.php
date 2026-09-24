@extends('layouts.app')

@section('title', 'Tambah Barang ATK - SBA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.items.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Tambah Barang ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Buat barang baru beserta kategori (inline) dan variasi (SKU) awalnya.
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

    <form action="{{ route('atk.items.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card 1: Main Product & Category Info -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-slate-100 pb-2">Informasi Barang & Kategori</h2>

            <!-- Category Option Toggle -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="toggle_new_category" name="create_new_category" value="1"
                        class="h-4 w-4 rounded border-gray-300 text-[#2563EB] focus:ring-[#2563EB]"
                        {{ old('create_new_category') ? 'checked' : '' }}
                        onchange="toggleCategoryInput()">
                    <label for="toggle_new_category" class="text-sm font-semibold text-[#0F172A] cursor-pointer">
                        Buat Kategori Baru (Inline)
                    </label>
                </div>

                <!-- Existing Category Selection -->
                <div id="existing_category_wrapper" class="space-y-2">
                    <label for="category_id" class="block text-sm font-semibold text-[#0F172A]">Kategori ATK <span class="text-red-500">*</span></label>
                    <select name="category_id" id="category_id" required
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                        <option value="" disabled selected>Pilih Kategori...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- New Category Fields (Hidden by default) -->
                <div id="new_category_wrapper" class="hidden space-y-4 border border-dashed border-slate-200 rounded-lg p-4 bg-slate-50">
                    <div class="space-y-2">
                        <label for="new_category_name" class="block text-sm font-semibold text-[#0F172A]">Nama Kategori Baru <span class="text-red-500">*</span></label>
                        <input type="text" name="new_category_name" id="new_category_name" value="{{ old('new_category_name') }}" placeholder="Contoh: Kertas & Catatan"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                    </div>
                    <div class="space-y-2">
                        <label for="new_category_description" class="block text-sm font-semibold text-[#0F172A]">Deskripsi Kategori Baru</label>
                        <input type="text" name="new_category_description" id="new_category_description" value="{{ old('new_category_description') }}" placeholder="Deskripsi opsional..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
                    </div>
                </div>
            </div>

            <!-- Item Name -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-[#0F172A]">Nama Barang <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Kertas HVS A4"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-semibold text-[#0F172A]">Deskripsi Barang</label>
                <textarea name="description" id="description" rows="3" placeholder="Tulis deskripsi detail tentang barang ini..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm">{{ old('description') }}</textarea>
            </div>

            <!-- Item Status -->
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-[#0F172A]">Status Barang</label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}
                            class="h-4 w-4 border-gray-300 text-[#2563EB] focus:ring-[#2563EB]">
                        <span class="text-sm font-medium text-slate-700">Tidak Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 2: Initial Variants Section -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                <div>
                    <h2 class="text-lg font-bold text-[#0F172A]">Varian Awal</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Satu barang minimal wajib memiliki 1 varian agar bisa dicatat di stok.</p>
                </div>
                <button type="button" onclick="addVariantRow()" class="bg-[#EFF6FF] text-[#2563EB] rounded px-3 py-1.5 font-bold text-xs flex items-center gap-1 hover:bg-[#DBEAFE] transition-colors">
                    <span class="material-symbols-outlined text-[16px]">add</span> Tambah Baris Varian
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-[#475569] min-w-[800px]">
                    <thead>
                        <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                            <th class="py-2.5 px-4 w-[25%]">Nama Varian <span class="text-red-500">*</span></th>
                            <th class="py-2.5 px-4 w-[15%]">Satuan <span class="text-red-500">*</span></th>
                            <th class="py-2.5 px-4 w-[15%]">Harga Beli <span class="text-red-500">*</span></th>
                            <th class="py-2.5 px-4 w-[15%]">Stok Awal <span class="text-red-500">*</span></th>
                            <th class="py-2.5 px-4 w-[15%]">Min. Stok <span class="text-red-500">*</span></th>
                            <th class="py-2.5 px-4 w-[12%]">Status</th>
                            <th class="py-2.5 px-4 text-center w-[3%]"></th>
                        </tr>
                    </thead>
                    <tbody id="variant_rows" class="divide-y divide-[#E5E7EB]">
                        @php
                            $oldVariants = old('variants', [['name' => '', 'unit' => '', 'purchase_price' => '0', 'stock' => '0', 'minimum_stock' => '5', 'is_active' => '1']]);
                        @endphp
                        @foreach($oldVariants as $index => $varVal)
                            <tr class="variant-row" data-index="{{ $index }}">
                                <td class="py-3 px-3">
                                    <input type="text" name="variants[{{ $index }}][name]" value="{{ $varVal['name'] ?? '' }}" required placeholder="Contoh: 80 Gram, Biru"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="text" name="variants[{{ $index }}][unit]" value="{{ $varVal['unit'] ?? '' }}" required placeholder="Contoh: Rim, Pcs"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" name="variants[{{ $index }}][purchase_price]" value="{{ $varVal['purchase_price'] ?? '0' }}" min="0" required placeholder="0"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" name="variants[{{ $index }}][stock]" value="{{ $varVal['stock'] ?? '0' }}" min="0" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" name="variants[{{ $index }}][minimum_stock]" value="{{ $varVal['minimum_stock'] ?? '5' }}" min="0" required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                </td>
                                <td class="py-3 px-3">
                                    <select name="variants[{{ $index }}][is_active]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                                        <option value="1" {{ ($varVal['is_active'] ?? '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                        <option value="0" {{ ($varVal['is_active'] ?? '1') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <button type="button" onclick="removeVariantRow(this)" class="text-red-500 hover:text-red-700 transition-colors hidden delete-btn flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-3">
            <a href="{{ route('atk.items.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 bg-[#2563EB] text-white rounded text-sm font-semibold hover:bg-[#1D4ED8] transition-colors shadow-sm">
                Simpan Barang
            </button>
        </div>
    </form>
</div>

<script>
    let variantIndex = {{ count($oldVariants) }};

    function toggleCategoryInput() {
        const toggle = document.getElementById('toggle_new_category');
        const existingWrapper = document.getElementById('existing_category_wrapper');
        const newWrapper = document.getElementById('new_category_wrapper');
        
        const categoryIdSelect = document.getElementById('category_id');
        const newCatName = document.getElementById('new_category_name');
        
        if (toggle.checked) {
            existingWrapper.classList.add('hidden');
            newWrapper.classList.remove('hidden');
            categoryIdSelect.removeAttribute('required');
            categoryIdSelect.disabled = true;
            newCatName.setAttribute('required', 'required');
        } else {
            existingWrapper.classList.remove('hidden');
            newWrapper.classList.add('hidden');
            categoryIdSelect.setAttribute('required', 'required');
            categoryIdSelect.disabled = false;
            newCatName.removeAttribute('required');
        }
    }

    function addVariantRow() {
        const tbody = document.getElementById('variant_rows');
        const newRow = document.createElement('tr');
        newRow.className = 'variant-row';
        newRow.setAttribute('data-index', variantIndex);
        
        newRow.innerHTML = `
            <td class="py-3 px-3">
                <input type="text" name="variants[${variantIndex}][name]" required placeholder="Contoh: 80 Gram, Biru"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
            </td>
            <td class="py-3 px-3">
                <input type="text" name="variants[${variantIndex}][unit]" required placeholder="Contoh: Rim, Pcs"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
            </td>
            <td class="py-3 px-3">
                <input type="number" name="variants[${variantIndex}][purchase_price]" value="0" min="0" required placeholder="0"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
            </td>
            <td class="py-3 px-3">
                <input type="number" name="variants[${variantIndex}][stock]" value="0" min="0" required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
            </td>
            <td class="py-3 px-3">
                <input type="number" name="variants[${variantIndex}][minimum_stock]" value="5" min="0" required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
            </td>
            <td class="py-3 px-3">
                <select name="variants[${variantIndex}][is_active]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-xs">
                    <option value="1" selected>Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </td>
            <td class="py-3 px-3 text-center">
                <button type="button" onclick="removeVariantRow(this)" class="text-red-500 hover:text-red-700 transition-colors delete-btn flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        variantIndex++;
        updateDeleteButtons();
    }

    function removeVariantRow(button) {
        const row = button.closest('.variant-row');
        row.remove();
        updateDeleteButtons();
    }

    function updateDeleteButtons() {
        const rows = document.querySelectorAll('.variant-row');
        rows.forEach((row, i) => {
            const btn = row.querySelector('.delete-btn');
            if (rows.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }

    // Run toggle on load to restore state if validation fails
    document.addEventListener('DOMContentLoaded', function() {
        toggleCategoryInput();
        updateDeleteButtons();
    });
</script>
@endsection
