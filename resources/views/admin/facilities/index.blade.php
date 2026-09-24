@extends('layouts.app')

@section('title', 'Manajemen Fasilitas - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Manajemen Fasilitas
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Kelola master data fasilitas pendukung ruangan meeting seperti AC, proyektor, papan tulis, dan koneksi internet.
            </p>
        </div>
        <a href="{{ route('admin.facilities.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Buat Fasilitas
        </a>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama Fasilitas</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Deskripsi</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($facilities as $facility)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-[#0F172A]">{{ $facility->name }}</td>
                                <td class="px-6 py-4 text-[#475569] font-medium">{{ $facility->description ?: '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.facilities.edit', $facility) }}" 
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:bg-slate-200 transition-colors"
                                            style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                            Edit
                                        </a>
                                        
                                        <form action="{{ route('admin.facilities.destroy', $facility) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus fasilitas {{ $facility->name }}? Menghapus ini juga akan melepaskannya dari semua ruangan.');">
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
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic bg-white">
                                    Belum ada fasilitas yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($facilities->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB] bg-slate-50/50">
                    {{ $facilities->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
