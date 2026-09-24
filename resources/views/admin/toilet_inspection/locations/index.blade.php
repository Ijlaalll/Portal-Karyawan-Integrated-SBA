@extends('layouts.app')

@section('title', 'Manajemen Lokasi - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Lokasi Toilet
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Kelola gedung, lantai, area, serta fasilitas toilet untuk inspeksi berkala.
            </p>
        </div>
        <a href="{{ route('admin.toilet_inspection.locations.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Tambah Lokasi
        </a>
    </header>

    @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 p-4 border border-green-200 text-sm font-medium text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-[#E5E7EB] rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gedung</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lantai</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Area</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama Toilet</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Gender</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                    @forelse ($locations as $loc)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-[#0F172A]">{{ $loc->building }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569] font-medium">{{ $loc->floor }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-[#475569] font-medium">{{ $loc->area }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-800">{{ $loc->toilet_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                    {{ $loc->gender === 'pria' ? 'Pria' : ($loc->gender === 'wanita' ? 'Wanita' : ($loc->gender === 'unisex' ? 'Unisex' : ucfirst($loc->gender))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                 @if ($loc->is_active)
                                     <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">Aktif</span>
                                 @else
                                     <span class="inline-flex items-center rounded-md bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-300">Tidak Aktif</span>
                                 @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                <div class="flex items-center justify-end gap-2">
                                     <a href="{{ route('admin.toilet_inspection.locations.edit', $loc) }}" 
                                         class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:bg-slate-200 transition-colors"
                                         style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                         <span class="material-symbols-outlined text-[15px]">edit</span>
                                         Edit
                                     </a>
                                     <form action="{{ route('admin.toilet_inspection.locations.destroy', $loc) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi {{ $loc->toilet_name }}? Tindakan ini tidak dapat dibatalkan.');">
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
                             <td colspan="7" class="px-6 py-10 text-center text-slate-400 italic">Tidak ada lokasi yang ditemukan.</td>
                         </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($locations->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-slate-50/50">
                {{ $locations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
