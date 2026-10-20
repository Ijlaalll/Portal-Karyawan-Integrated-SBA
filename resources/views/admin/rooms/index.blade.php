@extends('layouts.app')

@section('title', 'Manajemen Ruangan - Admin SBA')

@section('content')
<div class="min-h-full">
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Manajemen Ruangan
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Kelola daftar ruangan meeting, lokasi, kapasitas pengguna, serta ketersediaan fasilitas pendukung.
            </p>
        </div>
        <a href="{{ route('admin.rooms.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors shrink-0">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Buat Ruangan
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
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Lokasi</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Kapasitas</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Fasilitas</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($rooms as $room)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-[#0F172A]">
                                    <div class="flex items-center gap-3">
                                        @if($room->image_url)
                                            <img src="{{ $room->image_url }}" alt="{{ $room->name }}" class="w-10 h-10 rounded-lg object-cover bg-slate-50 border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-[#2563EB] shrink-0">
                                                <span class="material-symbols-outlined text-[20px]">meeting_room</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-bold text-[#0F172A]">{{ $room->name }}</div>
                                            <div class="text-[11px] text-slate-400 font-normal">ID: #{{ $room->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-[#475569] font-medium">{{ $room->location }}</td>
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-slate-700">{{ $room->capacity }} orang</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold {{ $room->isActive() ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' }}">
                                        {{ $room->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($room->facilities as $fac)
                                            <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-200">
                                                {{ $fac->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">Tidak ada fasilitas</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.rooms.show', $room) }}" 
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:opacity-80 transition-opacity"
                                            style="background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                                            <span class="material-symbols-outlined text-[15px]">visibility</span>
                                            Lihat
                                        </a>
                                        <a href="{{ route('admin.rooms.edit', $room) }}" 
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:bg-slate-200 transition-colors"
                                            style="background-color: #F8FAFC; color: #334155; border: 1px solid #CBD5E1;">
                                            <span class="material-symbols-outlined text-[15px]">edit</span>
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400 italic bg-white">
                                    Belum ada ruangan yang dibuat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if ($rooms->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB] bg-slate-50/50">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
