@extends('layouts.app')

@section('title', 'Manajemen Item Checklist - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Pertanyaan Checklist
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Kelola item checklist dinamis untuk formulir inspeksi toilet.
            </p>
        </div>
        <a href="{{ route('admin.toilet_inspection.checklist-items.create') }}" 
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm cursor-pointer shrink-0"
            style="background-color: #2563EB;">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Pertanyaan Checklist Baru
        </a>
    </header>

    @if (session('success'))
        <div class="mb-6 rounded-md p-4 text-sm font-medium" style="background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-2xl overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama Pertanyaan</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Deskripsi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($items as $item)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4.5 whitespace-nowrap font-bold text-[#0F172A]">{{ $item->name }}</td>
                        <td class="px-6 py-4.5 text-[#475569] font-medium">{{ $item->description ?: '-' }}</td>
                        <td class="px-6 py-4.5 whitespace-nowrap">
                            @if ($item->is_active)
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                    style="background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold"
                                    style="background-color: #F8FAFC; color: #64748B; border: 1px solid #CBD5E1;">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4.5 whitespace-nowrap text-right text-xs font-semibold">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.toilet_inspection.checklist-items.edit', $item) }}" 
                                    class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:bg-slate-200 transition-colors"
                                    style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                    <span class="material-symbols-outlined text-[15px]">edit</span>
                                    Edit
                                </a>
                                <form action="{{ route('admin.toilet_inspection.checklist-items.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan checklist ini? Tindakan ini akan menghapus catatan hasil inspeksi terkait.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                        class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:opacity-80 transition-opacity focus:outline-none cursor-pointer"
                                        style="background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;">
                                        <span class="material-symbols-outlined text-[15px]">delete</span>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">Tidak ada pertanyaan checklist yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
