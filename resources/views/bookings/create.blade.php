@extends('layouts.app')

@section('title', 'Pesan Ruangan - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <!-- Header -->
    <header class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">Pesan Ruangan</h1>
            <p class="text-sm text-[#475569] mt-1">Kelola dan pesan ruang meeting atau buat pertemuan online dalam satu workspace.</p>
        </div>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('bookings.index') }}" 
           onclick="clearFormState()"
           class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Kembali ke {{ auth()->user()->isAdmin() ? 'Dashboard' : 'Pemesanan Saya' }}
        </a>
    </header>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Harap perbaiki kesalahan berikut:</h3>
                    <ul class="list-disc pl-5 mt-2 text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Workspace Container (Stacked layout) -->
    <div class="flex flex-col gap-8">
        
        <!-- Booking Form and Room Selection (Full width) -->
        <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
                <form action="{{ route('bookings.store') }}" method="POST" onsubmit="clearFormState()">
                    @csrf

                    <div class="space-y-6">
                        <!-- 1. Meeting Type Selection -->
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-200/60 rounded-xl p-4">
                            <input type="hidden" name="meeting_type" value="offline">
                            <label class="relative flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" name="meeting_type" value="hybrid" id="meeting_type_hybrid" 
                                       {{ old('meeting_type', $meetingType) === 'hybrid' ? 'checked' : '' }}
                                       class="rounded border-[#E5E7EB] text-[#2563EB] focus:ring-[#2563EB] h-4 w-4 transition-all">
                                <span class="material-symbols-outlined text-slate-500 text-[20px]">settings_input_antenna</span>
                                <span class="text-xs font-bold text-slate-800">Aktifkan Kebutuhan Meeting Hybrid (GA & IT Support)</span>
                            </label>
                        </div>

                        <!-- 2. Meeting Parameters -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="min-w-0">
                                <label for="booking_date" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</label>
                                <input type="date" name="booking_date" id="booking_date" required value="{{ $date }}" min="{{ date('Y-m-d') }}"
                                       class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm"
                                       onchange="refreshWorkspace()">
                            </div>

                            <div class="min-w-0">
                                <label for="participant_count" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider">Jumlah Peserta</label>
                                <input type="number" name="participant_count" id="participant_count" required min="1" value="{{ $participantCount }}"
                                       class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm"
                                       onchange="refreshWorkspace()">
                            </div>

                            <div class="min-w-0">
                                <label for="start_time" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu Mulai</label>
                                <input type="time" name="start_time" id="start_time" required value="{{ $startTime }}"
                                       class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm"
                                       onchange="refreshWorkspace()">
                            </div>

                            <div class="min-w-0">
                                <label for="end_time" class="block text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu Selesai</label>
                                <input type="time" name="end_time" id="end_time" required value="{{ $endTime }}"
                                       class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm"
                                       onchange="refreshWorkspace()">
                            </div>
                        </div>

                        <!-- 3. Operating Hours and Duration Info -->
                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 text-[11px] text-slate-500">
                            <span class="font-bold text-slate-700 block mb-0.5">Ketentuan Pemesanan:</span>
                            <ul class="list-disc pl-4 space-y-0.5">
                                <li>Jam Operasional: <strong class="text-slate-600">08:00 – 17:00</strong></li>
                                <li>Durasi Minimal: <strong class="text-slate-600">30 Menit</strong></li>
                                <li>Slot terblokir otomatis saat bentrok dengan rapat yang disetujui.</li>
                            </ul>
                        </div>

                        <!-- 4. Room Selection Section -->
                        <div id="room-selection-section">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-3">
                                <div>
                                    <label class="block text-sm font-bold text-[#0F172A]">Pilih Ruangan Tersedia</label>
                                    <p class="text-xs text-slate-500">Pilih ruangan yang sesuai dengan kebutuhan dan kapasitas rapat Anda.</p>
                                </div>
                                @if($meetingType === 'offline' || $meetingType === 'hybrid')
                                    <span class="self-start sm:self-auto text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full">
                                        {{ $availableRooms->count() }} Ruangan Tersedia
                                    </span>
                                @endif
                            </div>
                            
                            @if($meetingType === 'offline' || $meetingType === 'hybrid')
                                @if($availableRooms->isEmpty())
                                    <div class="border border-dashed border-[#E5E7EB] rounded-xl p-8 text-center text-xs text-[#64748B] italic bg-slate-50">
                                        <span class="material-symbols-outlined text-[36px] text-slate-400 block mb-2">meeting_room</span>
                                        Tidak ada ruangan aktif yang memenuhi kriteria kapasitas & ketersediaan jadwal pada jam yang dipilih.
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        @foreach($availableRooms as $room)
                                            @php
                                                $isRoomSelected = old('room_id', $selectedRoomId) == $room->id;
                                            @endphp
                                            <div id="card_{{ $room->id }}" 
                                                 onclick="selectRoom({{ $room->id }})"
                                                 class="room-card group border-2 rounded-2xl overflow-hidden cursor-pointer transition-all duration-200 relative flex flex-col justify-between hover:shadow-md {{ $isRoomSelected ? 'border-[#2563EB] ring-2 ring-[#2563EB]/20 bg-blue-50/20 shadow-sm' : 'border-slate-200 bg-white hover:border-blue-300' }}">
                                                
                                                <!-- Room Image Banner -->
                                                <div class="relative w-full h-28 sm:h-36 bg-slate-100 overflow-hidden">
                                                    @if($room->image_url)
                                                        <img src="{{ $room->image_url }}" alt="{{ $room->name }}" 
                                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                    @else
                                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-blue-50 text-slate-400 group-hover:from-blue-50 group-hover:to-slate-100 transition-colors">
                                                            <span class="material-symbols-outlined text-[32px] sm:text-[38px] text-slate-400 group-hover:text-blue-500 transition-colors">meeting_room</span>
                                                            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5">Ruang Rapat</span>
                                                        </div>
                                                    @endif

                                                    <!-- Dark Gradient Overlay for Readability -->
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/15 pointer-events-none"></div>

                                                    <!-- Info (i) Button: Solid white with crisp blue icon -->
                                                    <button type="button" 
                                                            onclick="showRoomDetail(event, {{ json_encode([
                                                                'name' => $room->name,
                                                                'location' => $room->location,
                                                                'capacity' => $room->capacity,
                                                                'description' => $room->description ?: 'Tidak ada deskripsi tambahan untuk ruangan ini.',
                                                                'image' => $room->image_url,
                                                                'facilities' => $room->facilities->pluck('name')->toArray()
                                                            ]) }})"
                                                            class="absolute top-2 left-2 sm:top-2.5 sm:left-2.5 z-10 w-7 h-7 rounded-full bg-white text-[#2563EB] hover:bg-blue-50 flex items-center justify-center shadow-md border border-slate-200/80 transition-all active:scale-90"
                                                            title="Lihat Detail Fasilitas & Ruangan">
                                                        <span class="material-symbols-outlined text-[17px] font-bold">info</span>
                                                    </button>

                                                    <!-- Selected Checkmark Badge -->
                                                    <div class="checkmark-icon absolute top-2 right-2 sm:top-2.5 sm:right-2.5 z-10 w-7 h-7 rounded-full bg-[#2563EB] text-white flex items-center justify-center shadow-md transition-all duration-200 {{ $isRoomSelected ? '' : 'hidden' }}">
                                                        <span class="material-symbols-outlined text-[18px] font-bold">check</span>
                                                    </div>

                                                    <!-- Bottom Overlay on Image: Capacity & Location -->
                                                    <div class="absolute bottom-2 left-2 right-2 sm:left-2.5 sm:right-2.5 flex items-center justify-between text-white text-[11px] font-medium pointer-events-none">
                                                        <span class="inline-flex items-center gap-1 bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded-md text-[10px] font-semibold text-white">
                                                            <span class="material-symbols-outlined text-[12px] sm:text-[13px]">groups</span> {{ $room->capacity }} orang
                                                        </span>
                                                        <span class="inline-flex items-center gap-1 bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded-md text-[10px] font-semibold text-white truncate max-w-[120px] sm:max-w-[130px]">
                                                            <span class="material-symbols-outlined text-[12px] sm:text-[13px]">pin_drop</span> {{ $room->location }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Card Body Content -->
                                                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                                                    <div>
                                                        <h4 class="font-bold text-sm text-slate-900 group-hover:text-[#2563EB] transition-colors truncate" title="{{ $room->name }}">
                                                            {{ $room->name }}
                                                        </h4>

                                                        <!-- Facilities Chips -->
                                                        @if($room->facilities->isNotEmpty())
                                                            <div class="flex flex-wrap gap-1 mt-2 sm:mt-2.5">
                                                                @foreach($room->facilities->take(3) as $facility)
                                                                    <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-600 border border-slate-100">
                                                                        {{ $facility->name }}
                                                                    </span>
                                                                @endforeach
                                                                @if($room->facilities->count() > 3)
                                                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold text-slate-500" title="{{ $room->facilities->slice(3)->pluck('name')->implode(', ') }}">
                                                                        +{{ $room->facilities->count() - 3 }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <p class="text-[11px] text-slate-400 italic mt-2">Tidak ada fasilitas khusus</p>
                                                        @endif
                                                    </div>

                                                    <!-- Selection Footer Action -->
                                                    <div class="mt-2.5 sm:mt-3 pt-2 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                                        <span class="select-label font-bold {{ $isRoomSelected ? 'text-[#2563EB]' : 'text-slate-500 group-hover:text-[#2563EB]' }} transition-colors">
                                                            {{ $isRoomSelected ? 'Ruangan Dipilih' : 'Pilih Ruangan Ini' }}
                                                        </span>
                                                        <span class="select-icon material-symbols-outlined text-[16px] {{ $isRoomSelected ? 'text-[#2563EB]' : 'text-slate-400 group-hover:text-[#2563EB] group-hover:translate-x-0.5' }} transition-all">
                                                            {{ $isRoomSelected ? 'check_circle' : 'arrow_forward' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <input type="radio" name="room_id" value="{{ $room->id }}" id="room_{{ $room->id }}" class="hidden" 
                                                       {{ $isRoomSelected ? 'checked' : '' }}>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Hybrid Info Section -->
                        <div id="hybrid-info-section" class="border border-[#BFDBFE] bg-[#EFF6FF] rounded-xl p-4 text-xs text-[#1E3A8A] {{ $meetingType === 'hybrid' ? '' : 'hidden' }} transition-all">
                            <div class="flex gap-2.5">
                                <span class="material-symbols-outlined text-[#2563EB] text-[20px] shrink-0">info</span>
                                <div>
                                    <span class="font-bold block text-[#1E3A8A]">Layanan Meeting Hybrid Aktif</span>
                                    <p class="mt-0.5 text-slate-600">Tim IT Support akan menyiapkan perangkat audio visual, display/TV, kamera, peripheral, koneksi, serta troubleshooting teknis untuk kelancaran meeting hybrid Anda.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Details Form (Title and Agenda) -->
                        <div class="border-t border-[#E5E7EB] pt-5 space-y-4">
                            <div>
                                <label for="title" class="block text-sm font-semibold text-[#0F172A]">Judul Rapat / Kegiatan</label>
                                <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="Contoh: Rapat Koordinasi Kuartalan"
                                        class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm">
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-semibold text-[#0F172A]">Agenda Rapat (Deskripsi)</label>
                                <textarea name="description" id="description" rows="3" placeholder="Jelaskan secara singkat topik rapat..."
                                          class="mt-1 block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-sm">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <!-- 7. Additional Requirements Section -->
                        <div id="requirements-section" class="border-t border-[#E5E7EB] pt-5">
                            <label class="block text-sm font-bold text-[#0F172A] mb-3">Kebutuhan Tambahan (GA)</label>
                            
                            <div class="space-y-2.5">
                                @foreach(['Konsumsi / Makan', 'Snack', 'Air Minum', 'Perlengkapan Meeting'] as $reqName)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="additional_requirements[]" value="{{ $reqName }}"
                                               class="rounded border-[#E5E7EB] text-[#2563EB] focus:ring-[#2563EB] h-4 w-4">
                                        <span class="text-xs font-semibold text-slate-700">{{ $reqName }}</span>
                                    </label>
                                @endforeach
                                
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="additional_requirements[]" value="Lainnya" id="req_other_checkbox" onchange="toggleOtherTextarea()"
                                           class="rounded border-[#E5E7EB] text-[#2563EB] focus:ring-[#2563EB] h-4 w-4">
                                    <span class="text-xs font-semibold text-slate-700">Lainnya (Jelaskan)</span>
                                </label>
                                
                                <div id="other_textarea_container" class="hidden mt-2">
                                    <textarea name="additional_requirements_other" id="additional_requirements_other" rows="2" placeholder="Sebutkan kebutuhan perlengkapan/lainnya..."
                                              class="w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-[#0F172A] focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB] text-xs"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="mt-8 pt-5 border-t border-[#E5E7EB] flex justify-end gap-x-3">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('bookings.index') }}" 
                           onclick="clearFormState()"
                           class="rounded bg-white px-4 py-2 text-xs font-bold text-[#0F172A] shadow-sm ring-1 ring-inset ring-[#E5E7EB] hover:bg-[#F9FAFB]">Batal</a>
                        <button type="submit" class="rounded bg-[#2563EB] px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                            Konfirmasi Pemesanan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Calendar availability timeline (Full width at bottom) -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 shrink-0">
                    <div>
                        <h3 class="text-sm font-bold text-[#0F172A] uppercase tracking-wider">Jadwal Penggunaan Ruangan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Melihat daftar ketersediaan ruangan untuk tanggal <span class="font-bold text-[#2563EB]">{{ date('d M Y', strtotime($date)) }}</span></p>
                    </div>
                </div>

                <!-- Calendar Schedule Matrix -->
                <div class="flex-1 mt-6 flex flex-col overflow-x-auto">
                    <div class="min-w-[800px] flex flex-col border border-[#E5E7EB] rounded-lg overflow-hidden shadow-xs">
                        <!-- Hours Header Row -->
                        <div class="flex border-b border-[#E5E7EB] bg-[#F9FAFB] shrink-0 font-semibold text-[11px] text-[#475569]">
                            <div class="border-r border-[#E5E7EB] p-3 uppercase tracking-wider select-none bg-[#F9FAFB]" style="width: 150px; min-width: 150px; max-width: 150px; flex: none;">
                                Ruang Meeting
                            </div>
                            <div class="flex-1 grid text-center divide-x divide-[#E5E7EB] select-none bg-[#F9FAFB]" style="grid-template-columns: repeat(9, minmax(0, 1fr));">
                                <div class="py-2.5">08:00 - 09:00</div>
                                <div class="py-2.5">09:00 - 10:00</div>
                                <div class="py-2.5">10:00 - 11:00</div>
                                <div class="py-2.5">11:00 - 12:00</div>
                                <div class="py-2.5">12:00 - 13:00</div>
                                <div class="py-2.5">13:00 - 14:00</div>
                                <div class="py-2.5">14:00 - 15:00</div>
                                <div class="py-2.5">15:00 - 16:00</div>
                                <div class="py-2.5">16:00 - 17:00</div>
                            </div>
                        </div>
 
                        <!-- Room Rows -->
                        <div class="divide-y divide-[#E5E7EB] bg-white">
                            @forelse($allRooms as $room)
                                <div class="flex min-h-[60px] group hover:bg-[#F9FAFB]/50 transition-colors relative">
                                    <!-- Room Info -->
                                    <div class="border-r border-[#E5E7EB] p-3 flex flex-col justify-center bg-white group-hover:bg-[#F9FAFB]/50 z-10 overflow-hidden" style="width: 150px; min-width: 150px; max-width: 150px; flex: none;">
                                        <span class="font-bold text-[#0F172A] text-xs block truncate" title="{{ $room->name }}">{{ $room->name }}</span>
                                        <span class="text-[10px] text-[#475569] mt-0.5 block truncate" title="{{ $room->location }}">{{ $room->location }}</span>
                                        <span class="text-[10px] text-[#475569] italic block mt-0.5 truncate" title="Kapasitas: {{ $room->capacity }} orang">Kapasitas: {{ $room->capacity }} orang</span>
                                    </div>
 
                                    <!-- Timeline Grid Area -->
                                    <div class="flex-1 relative bg-white group-hover:bg-[#F9FAFB]/50">
                                        <!-- Background grid columns -->
                                        <div class="absolute inset-0 grid divide-x divide-[#E5E7EB] divide-dashed pointer-events-none" style="grid-template-columns: repeat(9, minmax(0, 1fr));">
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                            <div></div>
                                        </div>
 
                                        <!-- absolute-positioned booking overlays -->
                                        <div class="absolute inset-0 py-2.5 z-10 pr-1 pl-1">
                                            @foreach ($mappedBookings->where('room_id', $room->id) as $mb)
                                                @php
                                                    $bgColors = [
                                                        'pending' => 'bg-yellow-50 text-yellow-800 border-l-4 border-l-yellow-500 hover:bg-yellow-100 ring-1 ring-yellow-500/10 border-yellow-300/40',
                                                        'approved' => 'bg-green-50 text-green-800 border-l-4 border-l-green-500 hover:bg-green-100 ring-1 ring-green-500/10 border-green-300/40',
                                                        'completed' => 'bg-blue-50 text-blue-800 border-l-4 border-l-blue-500 hover:bg-blue-100 ring-1 ring-blue-500/10 border-blue-300/40',
                                                    ];
                                                    $colorClass = $bgColors[$mb['status']] ?? 'bg-gray-50 text-gray-800 border-l-4 border-l-gray-400 ring-1 ring-gray-500/10';
                                                    $ownerBorder = $mb['is_owner'] ? 'ring-2 ring-blue-600 ring-offset-1' : '';
                                                @endphp
                                                <div title="{{ $mb['title'] }} ({{ $mb['start_time'] }} - {{ $mb['end_time'] }})&#10;Penyelenggara: {{ $mb['user_name'] }}&#10;Agenda: {{ $mb['description'] ?: '-' }}"
                                                     class="absolute top-2 bottom-2 rounded shadow-xs px-2.5 py-0.5 overflow-hidden transition-all duration-200 {{ $colorClass }} {{ $ownerBorder }} flex flex-col justify-center select-none text-[10px] leading-tight"
                                                     style="left: calc({{ $mb['left'] }}% + 2px); width: calc({{ $mb['width'] }}% - 4px);">
                                                    <span class="font-bold truncate block">{{ $mb['title'] }}</span>
                                                    <span class="text-[9px] opacity-80 truncate block">{{ $mb['start_time'] }} - {{ $mb['end_time'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 text-center text-xs text-slate-400 italic bg-white w-full">Tidak ada ruangan aktif yang terdaftar.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Calendar Legend -->
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-xs text-[#475569] bg-white border border-[#E5E7EB] rounded-lg p-3 shadow-xs select-none">
                    <span class="font-semibold text-[#0F172A]">Legenda Status:</span>
                    <span class="flex items-center gap-x-1.5"><span class="w-3.5 h-3.5 rounded bg-yellow-50 border border-yellow-300 border-l-4 border-l-yellow-500 block"></span> Menunggu Persetujuan</span>
                    <span class="flex items-center gap-x-1.5"><span class="w-3.5 h-3.5 rounded bg-green-50 border border-green-300 border-l-4 border-l-green-500 block"></span> Disetujui / Aktif</span>
                    <span class="flex items-center gap-x-1.5"><span class="w-3.5 h-3.5 rounded bg-blue-50 border border-blue-300 border-l-4 border-l-blue-500 block"></span> Selesai</span>
                    <span class="flex items-center gap-x-1.5 ml-auto text-[11px]"><span class="material-symbols-outlined text-[15px]">info</span> Arahkan kursor pada pemesanan untuk melihat waktu dan penyelenggara.</span>
                </div>
            </div>

    </div>
</div>

<script>
    function initBookingCreateForm() {
        // Enforce toggle state on page load
        toggleMeetingType();
        toggleOtherTextarea();

        // Restore title and description
        if (sessionStorage.getItem('booking_title')) {
            document.getElementById('title').value = sessionStorage.getItem('booking_title');
        }
        if (sessionStorage.getItem('booking_description')) {
            document.getElementById('description').value = sessionStorage.getItem('booking_description');
        }
        if (sessionStorage.getItem('booking_req_other')) {
            const reqOther = document.getElementById('additional_requirements_other');
            if (reqOther) reqOther.value = sessionStorage.getItem('booking_req_other');
        }
        
        // Restore checkbox requirements
        const savedReqs = sessionStorage.getItem('booking_reqs');
        if (savedReqs) {
            const reqs = JSON.parse(savedReqs);
            reqs.forEach(val => {
                const el = document.querySelector(`input[name="additional_requirements[]"][value="${val}"]`);
                if (el) el.checked = true;
            });
            toggleOtherTextarea();
        }

        const checkbox = document.getElementById('meeting_type_hybrid');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                toggleMeetingType();
                refreshWorkspace();
            });
        }
    }
    if (window.onPageLoad) { window.onPageLoad(initBookingCreateForm); } else { document.addEventListener('DOMContentLoaded', initBookingCreateForm); }

    function getMeetingTypeValue() {
        const checkbox = document.getElementById('meeting_type_hybrid');
        return (checkbox && checkbox.checked) ? 'hybrid' : 'offline';
    }

    function saveFormState() {
        document.getElementById('title') && sessionStorage.setItem('booking_title', document.getElementById('title').value);
        document.getElementById('description') && sessionStorage.setItem('booking_description', document.getElementById('description').value);
        
        const reqOther = document.getElementById('additional_requirements_other');
        if (reqOther) {
            sessionStorage.setItem('booking_req_other', reqOther.value);
        }
        
        const reqCheckboxes = document.querySelectorAll('input[name="additional_requirements[]"]:checked');
        const reqVals = Array.from(reqCheckboxes).map(cb => cb.value);
        sessionStorage.setItem('booking_reqs', JSON.stringify(reqVals));
    }

    function refreshWorkspace() {
        saveFormState();
        
        const meetingType = getMeetingTypeValue();
        const date = document.getElementById('booking_date').value;
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const participantCount = document.getElementById('participant_count').value;
        
        let url = new URL(window.location.pathname, window.location.origin);
        url.searchParams.set('meeting_type', meetingType);
        url.searchParams.set('date', date);
        url.searchParams.set('start_time', startTime);
        url.searchParams.set('end_time', endTime);
        url.searchParams.set('participant_count', participantCount);
        
        const targetUrl = url.toString();
        if (window.navigateTo) { window.navigateTo(targetUrl); } else { window.location.href = targetUrl; }
    }
    
    function clearFormState() {
        sessionStorage.removeItem('booking_title');
        sessionStorage.removeItem('booking_description');
        sessionStorage.removeItem('booking_req_other');
        sessionStorage.removeItem('booking_reqs');
    }

    function toggleMeetingType() {
        const checkbox = document.getElementById('meeting_type_hybrid');
        const hybridInfo = document.getElementById('hybrid-info-section');
        
        if (checkbox && checkbox.checked) {
            if (hybridInfo) hybridInfo.classList.remove('hidden');
        } else {
            if (hybridInfo) hybridInfo.classList.add('hidden');
        }
    }

    function selectRoom(roomId) {
        const radio = document.getElementById('room_' + roomId);
        if (radio) {
            radio.checked = true;
        }
        
        document.querySelectorAll('.room-card').forEach(card => {
            card.classList.remove('border-[#2563EB]', 'ring-2', 'ring-[#2563EB]/20', 'bg-blue-50/20', 'shadow-sm');
            card.classList.add('border-slate-200', 'bg-white');
            card.querySelector('.checkmark-icon')?.classList.add('hidden');
            
            const label = card.querySelector('.select-label');
            if (label) {
                label.innerText = 'Pilih Ruangan Ini';
                label.className = 'select-label font-bold text-slate-500 group-hover:text-[#2563EB] transition-colors';
            }
            const icon = card.querySelector('.select-icon');
            if (icon) {
                icon.innerText = 'arrow_forward';
                icon.className = 'select-icon material-symbols-outlined text-[16px] text-slate-400 group-hover:text-[#2563EB] group-hover:translate-x-0.5 transition-all';
            }
        });
        
        const selectedCard = document.getElementById('card_' + roomId);
        if (selectedCard) {
            selectedCard.classList.remove('border-slate-200', 'bg-white');
            selectedCard.classList.add('border-[#2563EB]', 'ring-2', 'ring-[#2563EB]/20', 'bg-blue-50/20', 'shadow-sm');
            selectedCard.querySelector('.checkmark-icon')?.classList.remove('hidden');
            
            const label = selectedCard.querySelector('.select-label');
            if (label) {
                label.innerText = 'Ruangan Dipilih';
                label.className = 'select-label font-bold text-[#2563EB] transition-colors';
            }
            const icon = selectedCard.querySelector('.select-icon');
            if (icon) {
                icon.innerText = 'check_circle';
                icon.className = 'select-icon material-symbols-outlined text-[16px] text-[#2563EB] transition-all';
            }
        }
    }

    function toggleOtherTextarea() {
        const otherCheckbox = document.getElementById('req_other_checkbox');
        const container = document.getElementById('other_textarea_container');
        const textarea = document.getElementById('additional_requirements_other');
        
        if (otherCheckbox && otherCheckbox.checked) {
            container.classList.remove('hidden');
            textarea.setAttribute('required', 'required');
        } else if (container) {
            container.classList.add('hidden');
            textarea.removeAttribute('required');
        }
    }

    function showRoomDetail(event, room) {
        event.stopPropagation(); // Prevent card selection click trigger
        
        document.getElementById('modal-room-name').innerText = room.name;
        document.getElementById('modal-room-capacity').innerText = room.capacity;
        document.getElementById('modal-room-location').innerText = room.location;
        document.getElementById('modal-room-description').innerText = room.description;
        
        // Image setting
        const img = document.getElementById('modal-room-image');
        const placeholder = document.getElementById('modal-room-image-placeholder');
        if (room.image) {
            img.src = room.image;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
        
        // Facilities setting
        const container = document.getElementById('modal-room-facilities');
        container.innerHTML = '';
        if (room.facilities && room.facilities.length > 0) {
            room.facilities.forEach(fac => {
                const pill = document.createElement('span');
                pill.className = 'inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 border border-slate-200';
                pill.innerText = fac;
                container.appendChild(pill);
            });
        } else {
            const empty = document.createElement('span');
            empty.className = 'text-xs text-slate-400 italic';
            empty.innerText = 'Tidak ada fasilitas khusus.';
            container.appendChild(empty);
        }
        
        document.getElementById('room-detail-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function hideRoomDetail() {
        document.getElementById('room-detail-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>

<!-- Room Detail Modal -->
<div id="room-detail-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" onclick="hideRoomDetail()"></div>

    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 animate-in fade-in zoom-in duration-200">
            <!-- Close Button -->
            <button type="button" onclick="hideRoomDetail()" class="absolute top-4 right-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white/80 backdrop-blur-xs text-slate-500 hover:text-slate-700 shadow-xs border border-slate-200 hover:scale-105 transition-all">
                <span class="material-symbols-outlined text-[18px] font-bold">close</span>
            </button>

            <!-- Image Banner -->
            <div id="modal-room-image-container" class="relative h-48 w-full bg-gradient-to-r from-blue-500 to-indigo-600 overflow-hidden flex items-center justify-center">
                <img id="modal-room-image" src="" alt="Foto Ruangan" class="h-full w-full object-cover hidden">
                <!-- Fallback SVG/Vector illustration when no image is uploaded -->
                <div id="modal-room-image-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-white p-6">
                    <span class="material-symbols-outlined text-[48px] opacity-90 mb-2">meeting_room</span>
                    <span class="text-xs uppercase font-extrabold tracking-widest opacity-75">Detail Ruangan</span>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6 space-y-5">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900" id="modal-room-name">Nama Ruangan</h3>
                    
                    <!-- Metadata row -->
                    <div class="flex items-center gap-4 text-xs font-semibold text-slate-500 mt-2">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-[#2563EB]">groups</span> <span id="modal-room-capacity">X</span> orang</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[16px] text-[#2563EB]">pin_drop</span> <span id="modal-room-location">Lokasi</span></span>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deskripsi Ruangan</h4>
                    <p class="text-xs text-slate-600 leading-relaxed" id="modal-room-description">Deskripsi</p>
                </div>

                <!-- Facilities List -->
                <div class="space-y-2 border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fasilitas Tersedia</h4>
                    <div class="flex flex-wrap gap-1.5" id="modal-room-facilities">
                        <!-- Facilities pills go here -->
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="bg-slate-50 px-6 py-4 flex justify-end rounded-b-2xl border-t border-slate-100">
                <button type="button" onclick="hideRoomDetail()" class="rounded-lg bg-[#2563EB] px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-[#1D4ED8] transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
