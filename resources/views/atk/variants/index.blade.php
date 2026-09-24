@extends('layouts.app')

@section('title', 'Varian ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Varian ATK
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Kelola detail SKU, stok minimal, dan ketersediaan barang ATK.
            </p>
        </div>
        <a href="{{ route('atk.variants.create') }}" class="bg-[#2563EB] text-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">add</span> Tambah Varian
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

    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">SKU</th>
                        <th class="py-3.5 px-6">Nama Varian</th>
                        <th class="py-3.5 px-6">Barang Induk</th>
                        <th class="py-3.5 px-6">Stok Saat Ini</th>
                        <th class="py-3.5 px-6">Stok Minimum</th>
                        <th class="py-3.5 px-6">Ketersediaan</th>
                        <th class="py-3.5 px-6">Status</th>
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
                            <td class="py-4 px-6 font-semibold text-slate-500">{{ $variant->minimum_stock }} {{ $variant->unit }}</td>
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
                            <td class="py-4 px-6">
                                @if($variant->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-x-2">
                                <a href="{{ route('atk.variants.edit', $variant) }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                </a>
                                <form action="{{ route('atk.variants.destroy', $variant) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus varian ini? Jika varian memiliki riwayat mutasi atau permintaan, disarankan untuk menonaktifkannya saja.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center italic text-slate-400">Belum ada data varian.</td>
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
@endsection
