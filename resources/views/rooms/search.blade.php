@extends('layouts.app')

@section('title', 'Cari Ruangan - SBA')

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-[#0F172A] mb-1">Cari Ruangan Tersedia</h2>
        <p class="text-sm text-[#475569]">Cari dan pesan ruang meeting berdasarkan kebutuhan Anda.</p>
    </div>

    <!-- Search Form Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 mb-8 shadow-sm">
        <form action="{{ route('rooms.search') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div class="flex flex-col gap-1 min-w-0">
                <label for="date" class="text-xs font-semibold text-[#0F172A] uppercase tracking-wider">Tanggal</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-[18px]">event</span>
                    <input type="date" name="date" id="date" required value="{{ old('date', $date ?: date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                        class="w-full h-9 pl-10 pr-3 rounded border border-[#E5E7EB] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-white text-sm text-[#0F172A]">
                </div>
            </div>
            
            <div class="flex flex-col gap-1 min-w-0">
                <label for="start_time" class="text-xs font-semibold text-[#0F172A] uppercase tracking-wider">Waktu Mulai</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-[18px]">schedule</span>
                    <input type="time" name="start_time" id="start_time" required value="{{ old('start_time', $startTime ?: '09:00') }}"
                        class="w-full h-9 pl-10 pr-3 rounded border border-[#E5E7EB] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-white text-sm text-[#0F172A]">
                </div>
            </div>

            <div class="flex flex-col gap-1 min-w-0">
                <label for="end_time" class="text-xs font-semibold text-[#0F172A] uppercase tracking-wider">Waktu Selesai</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-[18px]">update</span>
                    <input type="time" name="end_time" id="end_time" required value="{{ old('end_time', $endTime ?: '10:00') }}"
                        class="w-full h-9 pl-10 pr-3 rounded border border-[#E5E7EB] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-white text-sm text-[#0F172A]">
                </div>
            </div>

            <div class="flex flex-col gap-1 min-w-0">
                <label for="capacity" class="text-xs font-semibold text-[#0F172A] uppercase tracking-wider">Kapasitas Min</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#475569] text-[18px]">groups</span>
                    <input type="number" name="capacity" id="capacity" min="1" value="{{ old('capacity', $capacity ?: 1) }}"
                        class="w-full h-9 pl-10 pr-3 rounded border border-[#E5E7EB] focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] bg-white text-sm text-[#0F172A]">
                </div>
            </div>

            <div class="col-span-1 sm:col-span-2 lg:col-span-4 flex justify-between mt-2 pt-4 border-t border-[#E5E7EB]">
                @if ($searched)
                    <a href="{{ route('rooms.search') }}" class="h-9 px-4 rounded border border-[#E5E7EB] text-[#0F172A] hover:bg-[#F9FAFB] text-sm flex items-center justify-center font-semibold">
                        Hapus Pencarian
                    </a>
                @else
                    <div></div>
                @endif
                <button type="submit" class="h-9 px-6 bg-[#2563EB] text-white rounded hover:bg-[#1D4ED8] transition-colors flex items-center gap-1.5 font-semibold text-sm shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    Cari Ruangan
                </button>
            </div>
        </form>
    </div>

    <!-- Results Section -->
    <div>
        <h3 class="text-lg font-bold text-[#0F172A] mb-4">
            {{ $searched ? 'Ruangan Tersedia' : 'Semua Ruang Meeting Aktif' }} ({{ $rooms->count() }})
        </h3>
        
        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                    <div class="ml-3">
                        <ul class="list-disc pl-5 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex flex-col gap-4">
            @forelse ($rooms as $room)
                <div class="bg-white border border-[#E5E7EB] rounded-lg p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-[#F9FAFB] transition-colors shadow-sm">
                    <div class="flex gap-4 min-w-0 flex-1">
                        <div class="w-16 h-16 rounded bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-3xl">meeting_room</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-base font-bold text-[#0F172A] truncate" title="{{ $room->name }}">{{ $room->name }}</h4>
                            <p class="text-xs text-[#475569] mt-0.5">{{ $room->location }} &bull; Maks {{ $room->capacity }} orang</p>
                            <div class="flex flex-wrap gap-1 mt-2">
                                @forelse ($room->facilities as $fac)
                                    <span class="inline-flex items-center rounded bg-gray-50 px-2 py-0.5 text-[10px] font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ $fac->name }}
                                    </span>
                                @empty
                                    <span class="text-[10px] text-[#475569] italic">Fasilitas tidak terdaftar</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto self-stretch sm:self-auto justify-end border-t sm:border-t-0 pt-3 sm:pt-0">
                        <a href="{{ route('calendar', ['room_id' => $room->id, 'date' => $date ?: date('Y-m-d')]) }}" class="rounded bg-white border border-[#E5E7EB] px-3.5 py-2 text-xs font-semibold text-[#0F172A] hover:bg-[#F9FAFB] shadow-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                            Kalender
                        </a>
                        
                        @if ($searched)
                            <a href="{{ route('bookings.create', ['room_id' => $room->id, 'date' => $date, 'start_time' => $startTime, 'end_time' => $endTime]) }}" class="rounded bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1D4ED8] shadow-sm flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add_task</span>
                                Pesan Slot
                            </a>
                        @else
                            <a href="{{ route('bookings.create', ['room_id' => $room->id]) }}" class="rounded bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white hover:bg-[#1D4ED8] shadow-sm flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add_box</span>
                                Pesan Ruangan
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white border border-dashed border-[#E5E7EB] rounded-lg py-12 px-6 text-center text-sm text-[#475569]">
                    Tidak ada ruangan tersedia yang cocok dengan kriteria Anda. Coba sesuaikan tanggal, waktu, atau kapasitas.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
