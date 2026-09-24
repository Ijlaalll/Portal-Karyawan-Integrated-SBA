@extends('layouts.app')

@section('title', 'Tinjauan Tertunda - SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-8">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Menunggu Ulasan
        </h1>
        <p class="text-sm text-[#475569] mt-1">
            Tinjau dan setujui atau tolak inspeksi toilet yang dikirimkan.
        </p>
    </header>

    <!-- Table -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
        <table class="min-w-full divide-y divide-[#E5E7EB]">
            <thead class="bg-[#F9FAFB]">
                <tr>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Petugas Inspeksi</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                @forelse ($pendingInspections as $ins)
                    <tr class="hover:bg-[#F8FAFC] transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ $ins->location->building }}, {{ $ins->location->floor }} — Toilet {{ $ins->location->gender === 'pria' ? 'Pria' : ($ins->location->gender === 'wanita' ? 'Wanita' : 'Unisex') }} ({{ $ins->location->toilet_name }})
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspector->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#475569]">{{ $ins->inspected_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">
                             <a href="{{ route('toilet_inspection.review', $ins) }}" class="text-[#2563EB] hover:text-[#1D4ED8]">
                                 Ulas
                             </a>
                        </td>
                    </tr>
                @empty
                     <tr>
                         <td colspan="4" class="px-6 py-12 text-center text-[#64748B]">Semua bersih! Tidak ada ulasan tertunda yang ditemukan.</td>
                     </tr>
                @endforelse
            </tbody>
        </table>
        @if($pendingInspections->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $pendingInspections->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
