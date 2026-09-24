@extends('layouts.app')

@section('title', 'Detail Ruangan - Admin SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Detail Ruangan: {{ $room->name }}
        </h1>
        <a href="{{ route('admin.rooms.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Kembali ke Ruangan
        </a>
    </header>
    
    <main>
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-[#E5E7EB] flex justify-between items-center bg-[#F9FAFB]">
                <div>
                    <h3 class="text-lg font-bold leading-6 text-[#0F172A]">{{ $room->name }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-[#475569]">{{ $room->location }}</p>
                </div>
                <a href="{{ route('admin.rooms.edit', $room) }}" class="rounded bg-[#2563EB] px-3.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                    Edit Ruangan
                </a>
            </div>
            <div class="border-t border-[#E5E7EB] px-6 py-5">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Kapasitas</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $room->capacity }} orang</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Status</dt>
                        <dd class="mt-1 text-sm">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $room->isActive() ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' }}">
                                {{ $room->status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-[#475569]">Deskripsi</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] whitespace-pre-line">{{ $room->description ?: 'Tidak ada deskripsi yang diberikan.' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-semibold text-[#475569] mb-2">Fasilitas</dt>
                        <dd class="text-sm text-[#0F172A]">
                            <div class="flex flex-wrap gap-2">
                                @forelse ($room->facilities as $fac)
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ $fac->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-[#475569] italic">Tidak ada fasilitas yang ditambahkan ke ruangan ini.</span>
                                @endforelse
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </main>
</div>
@endsection
