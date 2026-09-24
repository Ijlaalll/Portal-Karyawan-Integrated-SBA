@extends('layouts.app')

@section('title', 'Master ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Master ATK
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Kelola master data barang, varian, dan kategori alat tulis kantor.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('atk.categories.index') }}" class="border border-[#D1D5DB] text-slate-700 bg-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">category</span> Kelola Kategori
            </a>
            <a href="{{ route('atk.items.create') }}" class="bg-[#2563EB] text-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span> Tambah Barang
            </a>
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

    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Nama Barang</th>
                        <th class="py-3.5 px-6">Kategori</th>
                        <th class="py-3.5 px-6">Deskripsi</th>
                        <th class="py-3.5 px-6">Jumlah Varian</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">
                                <a href="{{ route('atk.items.show', $item) }}" class="hover:text-[#2563EB] hover:underline">{{ $item->name }}</a>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center rounded-md bg-[#EFF6FF] px-2 py-0.5 text-xs font-semibold text-[#2563EB] ring-1 ring-inset ring-[#2563EB]/10">
                                    {{ $item->category->name }}
                                </span>
                            </td>
                            <td class="py-4 px-6 max-w-xs truncate" title="{{ $item->description }}">{{ $item->description ?: '-' }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-700">{{ $item->variants->count() }} varian</td>
                            <td class="py-4 px-6">
                                @if($item->is_active)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-0.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-x-2">
                                <a href="{{ route('atk.items.show', $item) }}" class="text-xs font-bold text-green-700 hover:text-green-800 bg-green-50 px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span> Detail
                                </a>
                                <a href="{{ route('atk.items.edit', $item) }}" class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                                </a>
                                <form action="{{ route('atk.items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini? Penghapusan akan diblokir jika terdapat varian di dalamnya.');">
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
                            <td colspan="6" class="py-8 text-center italic text-slate-400">Belum ada data barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
