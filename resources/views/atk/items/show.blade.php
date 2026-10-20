@extends('layouts.app')

@section('title', 'Detail Barang ATK - SBA')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.items.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Detail Barang ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Lihat informasi detail barang dan kelola varian spesifiknya.
            </p>
        </div>
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

    <!-- Main Grid: Item Info and Variants List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side: Item Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-bold text-[#0F172A]">Informasi Barang</h2>
                    <a href="{{ route('atk.items.edit', $item) }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                    </a>
                </div>
                
                <!-- Product Image Banner / Card -->
                <div class="mb-4 rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex items-center justify-center min-h-[160px] relative">
                    @if($item->image_url)
                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-48 object-contain p-2 bg-white">
                    @else
                        <div class="text-center p-6 text-slate-400">
                            <span class="material-symbols-outlined text-[36px] text-slate-300">image_not_supported</span>
                            <p class="text-xs font-medium text-slate-400 mt-1">Belum ada foto barang</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Barang</span>
                        <span class="text-sm font-semibold text-[#0F172A]">{{ $item->name }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</span>
                        <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2 py-0.5 text-xs font-semibold text-[#2563EB] mt-1">
                            {{ $item->category->name }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Deskripsi</span>
                        <span class="text-xs text-slate-600 block mt-1 leading-relaxed">{{ $item->description ?: 'Tidak ada deskripsi.' }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Keaktifan</span>
                        <span class="inline-block mt-1">
                            @if($item->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Variants Table Card -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-[#E5E7EB] flex justify-between items-center bg-[#F9FAFB]">
                    <div>
                        <h2 class="text-lg font-bold text-[#0F172A]">Daftar Varian</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Semua SKU variasi untuk barang ini.</p>
                    </div>
                    <a href="{{ route('atk.variants.create', ['item_id' => $item->id]) }}" class="bg-[#2563EB] text-white rounded px-3 py-1.5 font-semibold text-xs flex items-center gap-1 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">add</span> Tambah Varian
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#475569]">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                                <th class="py-3 px-6">SKU</th>
                                <th class="py-3 px-6">Varian</th>
                                <th class="py-3 px-6">Satuan</th>
                                <th class="py-3 px-6">Harga Beli</th>
                                <th class="py-3 px-6">Stok / Ambang</th>
                                <th class="py-3 px-6">Ketersediaan</th>
                                <th class="py-3 px-6">Status</th>
                                <th class="py-3 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @forelse($item->variants as $variant)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3.5 px-6 font-mono text-xs font-semibold text-[#0F172A]">{{ $variant->sku }}</td>
                                    <td class="py-3.5 px-6 font-semibold text-slate-800">{{ $variant->name }}</td>
                                    <td class="py-3.5 px-6 text-slate-600">{{ $variant->unit }}</td>
                                    <td class="py-3.5 px-6 font-semibold text-slate-700">Rp{{ number_format($variant->purchase_price, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-6">
                                        <span class="font-bold {{ $variant->stock <= $variant->minimum_stock ? 'text-amber-600' : 'text-slate-700' }}">
                                            {{ $variant->stock }}
                                        </span>
                                        <span class="text-slate-400 text-xs">/ {{ $variant->minimum_stock }}</span>
                                        @if($variant->stock <= $variant->minimum_stock && $variant->stock > 0)
                                            <span class="block text-[9px] font-bold text-amber-600 uppercase tracking-wide">Stok Menipis</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($variant->stock > 0)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Tersedia</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">Habis</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if($variant->is_active)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-right flex items-center justify-end gap-x-2">
                                        <a href="{{ route('atk.variants.edit', $variant) }}" class="text-[11px] font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2 py-1 rounded transition-colors flex items-center gap-0.5">
                                            <span class="material-symbols-outlined text-[12px]">edit</span> Edit
                                        </a>
                                        <form action="{{ route('atk.variants.destroy', $variant) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus varian ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] font-bold text-red-600 hover:text-red-800 bg-red-50 px-2 py-1 rounded transition-colors flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[12px]">delete</span> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center italic text-slate-400">Belum ada data varian untuk barang ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
