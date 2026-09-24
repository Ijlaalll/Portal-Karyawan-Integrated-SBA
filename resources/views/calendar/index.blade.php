@extends('layouts.app')

@section('title', 'Kalender Ruangan - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-6">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Kalender Ketersediaan Ruangan
        </h1>
    </header>

    <main>
        <!-- Filters Panel -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-4 mb-6 shadow-sm">
            <form action="{{ route('calendar') }}" method="GET" id="calendarForm" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1">
                    <!-- Date Selection -->
                    <div>
                        <label for="date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Pilih Tanggal</label>
                        <div class="flex items-center gap-1.5 w-full sm:w-auto">
                            <button type="button" onclick="adjustDate(-1)" class="shrink-0 rounded bg-white border border-[#E5E7EB] px-2.5 py-1.5 text-[#0F172A] hover:bg-[#F9FAFB] shadow-sm font-semibold text-xs sm:text-sm">
                                Sebelumnya
                            </button>
                            <input type="date" name="date" id="date" value="{{ $date }}" onchange="submitFormWithTurbo(this)"
                                class="min-w-0 flex-1 sm:flex-initial rounded-md border border-[#E5E7EB] px-2 sm:px-3 py-1.5 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-xs sm:text-sm">
                            <button type="button" onclick="adjustDate(1)" class="shrink-0 rounded bg-white border border-[#E5E7EB] px-2.5 py-1.5 text-[#0F172A] hover:bg-[#F9FAFB] shadow-sm font-semibold text-xs sm:text-sm">
                                Berikutnya
                            </button>
                        </div>
                    </div>

                    <!-- Room Filter -->
                    <div class="flex-1 max-w-xs">
                        <label for="room_id" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Filter Ruangan</label>
                        <select name="room_id" id="room_id" onchange="submitFormWithTurbo(this)"
                            class="w-full rounded-md border border-[#E5E7EB] px-3 py-1.5 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm">
                            <option value="">Semua Ruangan</option>
                            @foreach ($allRooms as $r)
                                <option value="{{ $r->id }}" {{ $roomId == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="w-44">
                        <label for="status" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider mb-1">Filter Status</label>
                        <select name="status" id="status" onchange="submitFormWithTurbo(this)"
                            class="w-full rounded-md border border-[#E5E7EB] px-3 py-1.5 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm">
                            <option value="" {{ !$status ? 'selected' : '' }}>Pemesanan Aktif</option>
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                </div>

                <!-- Date shortcuts -->
                <div class="flex items-end">
                    <a href="{{ route('calendar', ['date' => date('Y-m-d'), 'room_id' => $roomId, 'status' => $status]) }}" 
                       class="rounded bg-white px-4 py-2 text-sm font-semibold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">
                        Hari Ini
                    </a>
                </div>
            </form>
        </div>

        <!-- Calendar View Box -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-x-auto shadow-sm">
            <div class="min-w-[1000px] flex flex-col">
                <!-- Hours Header Row -->
                <div class="flex border-b border-[#E5E7EB] bg-[#F9FAFB] shrink-0 font-semibold text-xs text-[#475569]">
                    <div class="w-[200px] shrink-0 border-r border-[#E5E7EB] p-3 uppercase tracking-wider">
                        Ruang Meeting
                    </div>
                    <div class="flex-1 flex text-center divide-x divide-[#E5E7EB]">
                        <div class="flex-1 py-3">08:00 - 09:00</div>
                        <div class="flex-1 py-3">09:00 - 10:00</div>
                        <div class="flex-1 py-3">10:00 - 11:00</div>
                        <div class="flex-1 py-3">11:00 - 12:00</div>
                        <div class="flex-1 py-3">12:00 - 13:00</div>
                        <div class="flex-1 py-3">13:00 - 14:00</div>
                        <div class="flex-1 py-3">14:00 - 15:00</div>
                        <div class="flex-1 py-3">15:00 - 16:00</div>
                        <div class="flex-1 py-3">16:00 - 17:00</div>
                    </div>
                </div>

                <!-- Room Rows -->
                <div class="divide-y divide-[#E5E7EB]">
                    @forelse ($rooms as $room)
                        <div class="flex min-h-[70px] group hover:bg-[#F9FAFB] transition-colors relative">
                            <!-- Room Info -->
                            <div class="w-[200px] shrink-0 border-r border-[#E5E7EB] p-3 flex flex-col justify-center bg-white group-hover:bg-[#F9FAFB] z-10">
                                <span class="font-bold text-[#0F172A]">{{ $room->name }}</span>
                                <span class="text-xs text-[#475569] mt-0.5">{{ $room->location }}</span>
                                <span class="text-[11px] text-[#475569] italic">Kapasitas: {{ $room->capacity }} orang</span>
                            </div>

                            <!-- Timeline Grid Area -->
                            <div class="flex-1 relative flex divide-x divide-[#E5E7EB] divide-dashed bg-white group-hover:bg-[#F9FAFB]">
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>
                                <div class="flex-1"></div>

                                <!-- absolute-positioned booking overlays -->
                                <div class="absolute inset-0 py-2.5 z-0 pr-1 pl-1">
                                    @foreach ($mappedBookings->where('room_id', $room->id) as $b)
                                        @php
                                            $bgColors = [
                                                'pending' => 'bg-yellow-50 text-yellow-800 border-l-4 border-l-yellow-500 hover:bg-yellow-100 ring-1 ring-yellow-500/10',
                                                'approved' => 'bg-green-50 text-green-800 border-l-4 border-l-green-500 hover:bg-green-100 ring-1 ring-green-500/10',
                                                'completed' => 'bg-blue-50 text-blue-800 border-l-4 border-l-blue-500 hover:bg-blue-100 ring-1 ring-blue-500/10',
                                            ];
                                            $colorClass = $bgColors[$b['status']] ?? 'bg-gray-50 text-gray-800 border-l-4 border-l-gray-400 ring-1 ring-gray-500/10';
                                        @endphp
                                        <a href="{{ route('bookings.show', $b['id']) }}"
                                           title="{{ $b['title'] }} ({{ $b['start_time'] }} - {{ $b['end_time'] }})&#10;Diselenggarakan oleh: {{ $b['user_name'] }}&#10;Agenda: {{ $b['description'] ?: '-' }}"
                                           class="absolute top-2 bottom-2 rounded shadow-sm px-3 py-1 overflow-hidden transition-all duration-200 {{ $colorClass }} flex flex-col justify-center select-none"
                                           style="left: calc({{ $b['left'] }}% + 2px); width: calc({{ $b['width'] }}% - 4px);">
                                            <span class="text-xs font-bold truncate block">{{ $b['title'] }}</span>
                                            <span class="text-[10px] opacity-80 truncate block">{{ $b['start_time'] }} - {{ $b['end_time'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center text-[#475569] italic bg-white">
                            Tidak ada ruangan yang terdaftar saat ini atau cocok dengan kriteria filter.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Calendar Legend -->
        <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-[#475569] bg-white border border-[#E5E7EB] rounded p-3 shadow-sm">
            <span class="font-semibold text-[#0F172A]">Legenda Status:</span>
            <span class="flex items-center gap-x-1.5"><span class="w-3 h-3 rounded bg-yellow-50 border border-yellow-300 border-l-4 border-l-yellow-500 block"></span> Menunggu Persetujuan</span>
            <span class="flex items-center gap-x-1.5"><span class="w-3 h-3 rounded bg-green-50 border border-green-300 border-l-4 border-l-green-500 block"></span> Disetujui / Aktif</span>
            <span class="flex items-center gap-x-1.5"><span class="w-3 h-3 rounded bg-blue-50 border border-blue-300 border-l-4 border-l-blue-500 block"></span> Selesai</span>
            <span class="flex items-center gap-x-1.5 ml-auto"><span class="material-symbols-outlined text-[14px]">info</span> Arahkan kursor pada pemesanan untuk melihat waktu dan penyelenggara.</span>
        </div>
    </main>
</div>

<script>
    function adjustDate(days) {
        const dateInput = document.getElementById('date');
        const currentDate = new Date(dateInput.value);
        currentDate.setDate(currentDate.getDate() + days);
        
        // Format to YYYY-MM-DD local timezone date
        const yyyy = currentDate.getFullYear();
        let mm = currentDate.getMonth() + 1;
        let dd = currentDate.getDate();
        if (dd < 10) dd = '0' + dd;
        if (mm < 10) mm = '0' + mm;
        
        dateInput.value = `${yyyy}-${mm}-${dd}`;
        document.getElementById('calendarForm').submit();
    }
</script>
@endsection
