@extends('layouts.app')

@section('title', 'Katalog ATK - SBA')

@section('content')
<div class="space-y-6 relative">
    <header class="flex flex-row justify-between items-center gap-3 border-b border-[#E5E7EB] pb-3 sm:pb-5">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-3xl font-bold leading-tight tracking-tight text-[#0F172A] truncate">
                Katalog ATK
            </h1>
            <p class="text-xs sm:text-sm text-[#475569] mt-0.5 hidden sm:block">
                Pilih alat tulis kantor yang Anda butuhkan dan ajukan ke General Affairs.
            </p>
        </div>
        <button type="button" onclick="toggleCartDrawer()" 
            class="flex items-center gap-1.5 sm:gap-2.5 px-3.5 py-2 sm:px-5 sm:py-2.5 bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-xl text-xs sm:text-sm font-bold shadow-sm transition-all relative shrink-0">
            <span class="material-symbols-outlined text-[18px] sm:text-[20px]">shopping_cart</span>
            <span>Keranjang</span>
            <span id="cart-badge-header" class="bg-white text-[#2563EB] text-[11px] sm:text-xs font-black px-1.5 py-0.5 rounded-full min-w-[18px] text-center">0</span>
        </button>
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

    <!-- Filters & Search Area (Compact Toolbar) -->
    <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-sm flex flex-col md:flex-row items-center gap-4 justify-between">
        <!-- Category Dropdown Selector -->
        <div class="relative w-full md:w-64">
            <select onchange="window.location.href = this.value" 
                class="block w-full rounded-lg border-[#E5E7EB] py-2 pl-3 pr-10 text-xs focus:border-[#2563EB] focus:ring-[#2563EB] bg-white text-[#0F172A] font-semibold shadow-sm">
                <option value="{{ route('atk.catalog') }}">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ route('atk.catalog', ['category_id' => $cat->id, 'search' => $search]) }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Search Input -->
        <form action="{{ route('atk.catalog') }}" method="GET" class="w-full md:w-80 flex items-center gap-2">
            @if($categoryId)
                <input type="hidden" name="category_id" value="{{ $categoryId }}">
            @endif
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="material-symbols-outlined text-[18px] text-slate-400">search</span>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari ATK..."
                    class="block w-full rounded-lg bg-white pl-9 pr-3 py-2 text-xs text-[#0F172A] focus:outline-none transition-colors"
                    style="border: 1px solid #CBD5E1;">
            </div>
            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-lg text-xs font-semibold shadow-sm transition-colors cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('atk.catalog', ['category_id' => $categoryId]) }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors shrink-0">Reset</a>
            @endif
        </form>
    </div>

    <!-- Catalog Grid (2 columns on mobile, 3-4 on tablet/desktop) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
        @forelse($variants as $variant)
            @php
                $availability = $variant->availability_status;
                $isOutOfStock = $variant->stock <= 0;
            @endphp
            <!-- Variant Card -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl sm:rounded-2xl overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col justify-between min-w-0 {{ $isOutOfStock ? 'opacity-80 bg-slate-50/80' : '' }}">
                
                <!-- Product Image Area -->
                <div class="relative w-full h-28 sm:h-44 bg-slate-50 border-b border-slate-100 flex items-center justify-center overflow-hidden group cursor-pointer"
                     onclick="openImageModal('{{ $variant->item->image_url ?? '' }}', '{{ addslashes($variant->item->name) }}', '{{ addslashes($variant->item->category->name) }}')">
                    @if($variant->item->image_url)
                        <img src="{{ $variant->item->image_url }}" alt="{{ $variant->item->name }}" loading="lazy" 
                             class="w-full h-full object-contain p-2 sm:p-2.5 group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity hidden sm:flex items-center justify-center">
                            <span class="bg-white/95 text-slate-700 text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1 backdrop-blur-xs">
                                <span class="material-symbols-outlined text-[15px] text-[#2563EB]">zoom_in</span> Perbesar
                            </span>
                        </div>
                    @else
                        <!-- Modern Fallback Placeholder -->
                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20 text-slate-400 p-2 sm:p-4 select-none">
                            <div class="w-8 h-8 sm:w-12 sm:h-12 rounded-xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-[#2563EB] mb-1 sm:mb-1.5">
                                <span class="material-symbols-outlined text-[20px] sm:text-[26px]">inventory_2</span>
                            </div>
                            <span class="text-[9px] sm:text-[11px] font-semibold text-slate-400 text-center line-clamp-1">{{ $variant->item->category->name }}</span>
                        </div>
                    @endif

                    @if($isOutOfStock)
                        <span class="absolute top-2 left-2 bg-rose-600 text-white text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-md shadow-xs flex items-center gap-0.5 sm:gap-1">
                            Habis
                        </span>
                    @endif
                </div>

                <!-- Card Content -->
                <div class="p-2.5 sm:p-5 flex-1 flex flex-col justify-between space-y-2 sm:space-y-4">
                    <div class="space-y-0.5 sm:space-y-1">
                        <span class="text-[9px] sm:text-[10px] font-bold text-slate-400 uppercase tracking-wider block truncate">
                            {{ $variant->item->category->name }}
                        </span>
                        <h3 class="font-bold text-[#0F172A] text-xs sm:text-sm leading-snug line-clamp-2" title="{{ $variant->item->name }}">
                            {{ $variant->item->name }}
                        </h3>
                        <p class="text-[11px] sm:text-xs text-[#2563EB] font-bold leading-none truncate" title="{{ $variant->name }}">
                            {{ $variant->name }}
                        </p>
                    </div>

                    <div class="space-y-1 sm:space-y-2">
                        <div class="flex items-center justify-between text-[10px] sm:text-xs border-b border-slate-100 pb-1.5 sm:pb-2">
                            <span class="text-slate-400 font-medium truncate max-w-[80px] sm:max-w-none">
                                <span class="hidden sm:inline">SKU: </span><span class="font-mono font-semibold text-slate-600">{{ $variant->sku }}</span>
                            </span>
                            @if($isOutOfStock)
                                <span class="inline-flex items-center text-red-600 font-bold gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] shrink-0">
                                    <span class="material-symbols-outlined text-[13px] sm:text-[14px]">cancel</span> Habis
                                </span>
                            @elseif($variant->stock <= $variant->minimum_stock)
                                <span class="inline-flex items-center text-amber-600 font-bold gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] shrink-0">
                                    <span class="material-symbols-outlined text-[13px] sm:text-[14px]">warning</span> Menipis
                                </span>
                            @else
                                <span class="inline-flex items-center text-emerald-600 font-bold gap-0.5 sm:gap-1 text-[10px] sm:text-[11px] shrink-0">
                                    <span class="material-symbols-outlined text-[13px] sm:text-[14px]">check_circle</span> Tersedia
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-[10px] sm:text-xs pt-0.5 sm:pt-1">
                            <span class="text-slate-500 font-medium">Stok:</span>
                            <span class="font-bold text-[#0F172A]">{{ $variant->stock }} {{ $variant->unit }}</span>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-3 pt-1 sm:pt-2">
                        @if($isOutOfStock)
                            <button type="button" disabled
                                class="w-full bg-slate-200 text-slate-400 rounded-lg py-1.5 sm:py-2.5 font-bold text-[10px] sm:text-xs cursor-not-allowed">
                                Stok Habis
                            </button>
                        @else
                            <!-- Quantity Control Inside Card -->
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] sm:text-xs font-semibold text-slate-500 pl-0.5 select-none hidden sm:inline">Jumlah</span>
                                <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50 w-full sm:w-auto justify-between sm:justify-start">
                                    <button type="button" onclick="decrementCardQty('{{ $variant->id }}')" 
                                        class="h-6 w-6 sm:h-7 sm:w-7 hover:bg-slate-200 text-[#0F172A] font-bold flex items-center justify-center transition-colors focus:outline-none">
                                        <span class="material-symbols-outlined text-[12px] sm:text-[14px]">remove</span>
                                    </button>
                                    <input type="number" id="card-qty-{{ $variant->id }}" value="1" min="1" max="{{ $variant->stock }}" readonly
                                        class="w-6 sm:w-8 text-center font-bold text-slate-800 text-[11px] sm:text-xs bg-transparent border-0 p-0 focus:ring-0 pointer-events-none">
                                    <button type="button" onclick="incrementCardQty('{{ $variant->id }}', {{ $variant->stock }}, '{{ $variant->unit }}')" 
                                        class="h-6 w-6 sm:h-7 sm:w-7 hover:bg-slate-200 text-[#0F172A] font-bold flex items-center justify-center transition-colors focus:outline-none">
                                        <span class="material-symbols-outlined text-[12px] sm:text-[14px]">add</span>
                                    </button>
                                </div>
                            </div>

                            <button type="button" 
                                onclick="addCardItemToCart('{{ $variant->id }}', '{{ addslashes($variant->item->name) }}', '{{ addslashes($variant->name) }}', {{ $variant->stock }}, '{{ $variant->unit }}', '{{ $variant->item->image_url ?? '' }}')"
                                class="w-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-lg py-1.5 sm:py-2.5 font-bold text-[10px] sm:text-xs transition-colors flex items-center justify-center gap-1 shadow-xs">
                                <span class="material-symbols-outlined text-[14px] sm:text-[16px]">add_shopping_cart</span>
                                <span><span class="hidden sm:inline">Tambah ke </span>Keranjang</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center italic text-slate-400 bg-white border border-slate-200 rounded-xl">
                Tidak ada barang ATK yang tersedia dalam katalog saat ini.
            </div>
        @endforelse
    </div>

    @if($variants->hasPages())
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-4 shadow-sm">
            {{ $variants->links() }}
        </div>
    @endif
</div>

<!-- Cart Sliding Drawer Backdrop -->
<div id="cart-backdrop" onclick="toggleCartDrawer()" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden" style="z-index: 90;"></div>

<!-- Cart Sliding Drawer Container -->
<div id="cart-drawer" class="fixed top-0 right-0 h-screen bg-white shadow-2xl border-l border-slate-200 flex flex-col justify-between" style="z-index: 100; transform: translateX(100%); transition: transform 0.3s ease-in-out; width: 450px; max-width: 100%;">
    <!-- Header -->
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center gap-2 text-[#2563EB]">
            <span class="material-symbols-outlined text-[24px]">shopping_basket</span>
            <h3 class="text-lg font-bold text-[#0F172A]">Keranjang Permintaan</h3>
        </div>
        <button type="button" onclick="toggleCartDrawer()" class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 rounded-lg hover:bg-slate-100 flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
    </div>

    <!-- Scrollable Cart Items List Container -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4" id="cart-items-container">
        <!-- Dynamic content injected here -->
    </div>

    <!-- Fixed Footer Form Submission -->
    <div class="p-6 border-t border-slate-100 bg-slate-50 space-y-4 shrink-0">
        <div class="space-y-1.5">
            <label for="cart-note" class="block text-xs font-bold text-slate-600 uppercase">Catatan / Keperluan <span class="text-slate-400">(Opsional)</span></label>
            <textarea id="cart-note" rows="3" placeholder="Tulis catatan keperluan pemakaian ATK untuk GA..."
                class="block w-full rounded-xl border-slate-200 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] text-xs transition-colors p-3 resize-none"></textarea>
        </div>

        <div class="text-[10px] text-slate-600 font-medium bg-blue-50/50 p-3 rounded-xl border border-blue-100 leading-relaxed flex items-start gap-2">
            <span class="material-symbols-outlined text-[15px] text-[#2563EB] shrink-0 mt-0.5">info</span>
            <span>Stok akan divalidasi ulang secara real-time terhadap database saat pengajuan dikirim.</span>
        </div>

        <div class="flex gap-3">
            <button type="button" onclick="toggleCartDrawer()" class="flex-1 py-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 bg-white hover:bg-slate-100 hover:border-slate-300 hover:text-slate-800 transition-all shadow-sm">
                Kembali Belanja
            </button>
            <button type="button" id="btn-submit-request" onclick="submitRequest()" disabled
                class="flex-1 py-3 bg-[#2563EB] text-white hover:bg-[#1D4ED8] disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center gap-1.5">
                Kirim Permintaan
            </button>
        </div>
    </div>
</div>

<!-- Hidden Request Submit Form -->
<form id="request-form" action="{{ route('atk.requests.store') }}" method="POST" class="hidden"></form>

<!-- Modal Zoom Foto Barang -->
<div id="image-zoom-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="zoom-modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="closeImageModal()"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md p-6 border border-slate-100">
            <div class="flex items-start justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <span id="zoom-modal-category" class="text-[10px] font-bold text-[#2563EB] uppercase tracking-wider bg-blue-50 px-2 py-0.5 rounded"></span>
                    <h3 class="text-base font-bold text-[#0F172A] mt-1" id="zoom-modal-title"></h3>
                </div>
                <button type="button" onclick="closeImageModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center p-3 min-h-[260px] max-h-[380px]">
                <img id="zoom-modal-img" src="#" alt="Foto ATK" class="w-full h-full max-h-[360px] object-contain rounded-lg">
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" onclick="closeImageModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    // Load cart from localStorage to persist
    if (localStorage.getItem('atk_cart')) {
        try {
            cart = JSON.parse(localStorage.getItem('atk_cart'));
        } catch(e) {
            cart = [];
        }
    }

    // Refresh UI on load
    updateCartUI();

    // Escape key listener to close drawer
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const drawer = document.getElementById('cart-drawer');
            if (drawer.style.transform === 'translateX(0px)') {
                toggleCartDrawer();
            }
        }
    });

    function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        const backdrop = document.getElementById('cart-backdrop');
        
        // Check current transform state
        const isClosed = drawer.style.transform === 'translateX(100%)' || drawer.style.transform === '';
        
        if (isClosed) {
            // Open Drawer
            drawer.style.transform = 'translateX(0px)';
            backdrop.classList.remove('hidden');
        } else {
            // Close Drawer
            drawer.style.transform = 'translateX(100%)';
            backdrop.classList.add('hidden');
        }
    }

    function incrementCardQty(variantId, maxStock, unit) {
        const input = document.getElementById(`card-qty-${variantId}`);
        let val = parseInt(input.value) || 1;
        if (val < maxStock) {
            input.value = val + 1;
        } else {
            alert(`Tidak dapat melebihi stok yang tersedia (${maxStock} ${unit})`);
        }
    }

    // Decrement Card Qty Selector
    function decrementCardQty(variantId) {
        const input = document.getElementById(`card-qty-${variantId}`);
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
        }
    }

    function addCardItemToCart(variantId, name, variantName, stock, unit, image) {
        const qtyInput = document.getElementById(`card-qty-${variantId}`);
        const qtyToAdd = parseInt(qtyInput.value) || 1;

        // Find existing in cart
        const existing = cart.find(item => item.variant_id == variantId);
        
        if (existing) {
            if (existing.quantity + qtyToAdd <= stock) {
                existing.quantity += qtyToAdd;
                if (!existing.image && image) {
                    existing.image = image;
                }
            } else {
                alert(`Tidak dapat menambah quantity. Total di keranjang melebihi stok tersedia (${stock} ${unit})`);
                return;
            }
        } else {
            cart.push({
                variant_id: variantId,
                name: name,
                variant_name: variantName,
                stock: stock,
                unit: unit,
                quantity: qtyToAdd,
                image: image || ''
            });
        }
        
        // Reset Card Selector to 1
        qtyInput.value = 1;
        
        saveCart();
        updateCartUI();
        
        // Open drawer slide automatically when items are added
        const drawer = document.getElementById('cart-drawer');
        if (drawer.style.transform === 'translateX(100%)' || drawer.style.transform === '') {
            toggleCartDrawer();
        }
    }

    function removeFromCart(variantId) {
        cart = cart.filter(item => item.variant_id != variantId);
        saveCart();
        updateCartUI();
    }

    function updateQty(variantId, change) {
        const item = cart.find(item => item.variant_id == variantId);
        if (!item) return;

        const newQty = item.quantity + change;
        if (newQty < 1) {
            removeFromCart(variantId);
            return;
        }

        if (newQty > item.stock) {
            alert(`Jumlah melebihi stok yang tersedia (${item.stock} ${item.unit})`);
            return;
        }

        item.quantity = newQty;
        saveCart();
        updateCartUI();
    }

    function saveCart() {
        localStorage.setItem('atk_cart', JSON.stringify(cart));
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function updateCartUI() {
        const container = document.getElementById('cart-items-container');
        const badgeHeader = document.getElementById('cart-badge-header');
        const submitBtn = document.getElementById('btn-submit-request');

        // Calculate total count
        const totalItems = cart.reduce((acc, curr) => acc + curr.quantity, 0);
        
        // Update header button badge
        if (badgeHeader) {
            badgeHeader.textContent = totalItems;
        }

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                    <div class="h-16 w-16 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center mb-2">
                        <span class="material-symbols-outlined text-[32px]">shopping_cart</span>
                    </div>
                    <div class="space-y-1">
                        <p class="font-bold text-[#0F172A] text-sm">Keranjang Anda masih kosong</p>
                        <p class="text-xs text-slate-400 max-w-[240px] mx-auto leading-relaxed">Pilih barang dari katalog di sebelah kiri untuk menambahkannya ke pengajuan.</p>
                    </div>
                    <button type="button" onclick="toggleCartDrawer()" class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                        Mulai Belanja
                    </button>
                </div>
            `;
            submitBtn.disabled = true;
            return;
        }

        submitBtn.disabled = false;
        let html = '<div class="space-y-4">';
        cart.forEach(item => {
            const safeName = escapeHtml(item.name || '');
            const safeVariantName = escapeHtml(item.variant_name || '');
            const safeUnit = escapeHtml(item.unit || '');
            const safeStock = escapeHtml(String(item.stock || 0));
            const safeQty = escapeHtml(String(item.quantity || 0));
            const safeVariantId = escapeHtml(String(item.variant_id || ''));
            const safeImage = item.image ? escapeHtml(item.image) : '';

            const imageHtml = safeImage 
                ? `<img src="${safeImage}" alt="${safeName}" class="w-11 h-11 rounded-lg object-contain bg-slate-50 border border-slate-200 shrink-0 p-1">`
                : `<div class="w-11 h-11 rounded-lg bg-slate-100 text-slate-400 border border-slate-200 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[20px]">inventory_2</span></div>`;

            html += `
                <div class="p-3.5 border border-slate-200 rounded-xl flex items-center justify-between gap-3 text-xs bg-white shadow-sm hover:shadow transition-all">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        ${imageHtml}
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-[#0F172A] truncate text-xs leading-snug" title="${safeName}">${safeName}</p>
                            <p class="text-slate-500 font-semibold text-[11px] mt-0.5 truncate">${safeVariantName}</p>
                            <p class="text-[10px] text-slate-400 mt-1 font-bold bg-slate-50 inline-block px-1.5 py-0.5 rounded border border-slate-100">Stok: ${safeStock} ${safeUnit}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-slate-50">
                            <!-- Decrement button -->
                            <button type="button" onclick="updateQty('${safeVariantId}', -1)" 
                                class="h-7 w-7 hover:bg-slate-200 text-[#0F172A] font-bold flex items-center justify-center transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-[14px]">remove</span>
                            </button>
                            <span class="w-7 text-center font-bold text-slate-800 text-xs select-none">${safeQty}</span>
                            <!-- Increment button -->
                            <button type="button" onclick="updateQty('${safeVariantId}', 1)" 
                                class="h-7 w-7 hover:bg-slate-200 text-[#0F172A] font-bold flex items-center justify-center transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-[14px]">add</span>
                            </button>
                        </div>
                        <!-- Remove button -->
                        <button type="button" onclick="removeFromCart('${safeVariantId}')" 
                            class="h-7 w-7 rounded-lg text-red-500 bg-red-50 hover:bg-red-100 flex items-center justify-center transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function submitRequest() {
        if (cart.length === 0) return;

        if (!confirm('Apakah Anda yakin ingin mengirim permintaan ATK ini?')) return;

        const form = document.getElementById('request-form');
        form.innerHTML = ''; // Clear previous inputs
        
        // Add CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add Note
        const note = document.getElementById('cart-note').value;
        const noteInput = document.createElement('input');
        noteInput.type = 'hidden';
        noteInput.name = 'note';
        noteInput.value = note;
        form.appendChild(noteInput);
        
        // Add Items
        cart.forEach((item, index) => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = `items[${index}][variant_id]`;
            idInput.value = item.variant_id;
            form.appendChild(idInput);
            
            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = `items[${index}][quantity]`;
            qtyInput.value = item.quantity;
            form.appendChild(qtyInput);
        });

        // Clear local storage cart on submission
        localStorage.removeItem('atk_cart');
        
        form.submit();
    }

    function openImageModal(imageUrl, itemName, categoryName) {
        if (!imageUrl) return;
        document.getElementById('zoom-modal-img').src = imageUrl;
        document.getElementById('zoom-modal-title').textContent = itemName;
        document.getElementById('zoom-modal-category').textContent = categoryName;
        document.getElementById('image-zoom-modal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('image-zoom-modal').classList.add('hidden');
        document.getElementById('zoom-modal-img').src = '#';
    }
</script>
@endsection
