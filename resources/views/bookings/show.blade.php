@extends('layouts.app')

@section('title', 'Detail Pemesanan - SBA')

@section('content')
<div class="max-w-3xl mx-auto">
    <header class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Detail Pemesanan
        </h1>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.booking_history') : route('bookings.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8]">
            Kembali ke {{ auth()->user()->isAdmin() ? 'Riwayat Penggunaan Ruangan' : 'Pemesanan Saya' }}
        </a>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

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

        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-[#E5E7EB] flex justify-between items-center bg-[#F9FAFB]">
                <div>
                    <h3 class="text-lg font-bold leading-6 text-[#0F172A]">{{ $booking->title }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-[#475569]">{{ $booking->room ? $booking->room->name : 'Pertemuan Online' }}</p>
                </div>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
                        'approved' => 'bg-green-50 text-green-700 ring-green-600/20',
                        'rejected' => 'bg-red-50 text-red-700 ring-red-600/20',
                        'cancelled' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                        'completed' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                    ];
                    $colorClass = $statusColors[$booking->status] ?? 'bg-gray-50 text-gray-700';
                @endphp
                <span class="inline-flex items-center rounded-md px-3 py-1 text-sm font-semibold {{ $colorClass }} ring-1 ring-inset">
                    {{ $booking->status === 'pending' ? 'Menunggu' : ($booking->status === 'approved' ? 'Disetujui' : ($booking->status === 'rejected' ? 'Ditolak' : ($booking->status === 'completed' ? 'Selesai' : ($booking->status === 'cancelled' ? 'Dibatalkan' : ucfirst($booking->status))))) }}
                </span>
            </div>

            <div class="px-6 py-5">
                @if($booking->status === 'cancelled')
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 text-[#7F1D1D] text-xs">
                        <div class="flex items-center gap-2 font-bold mb-2">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            <span class="text-sm">Informasi Detail Pembatalan</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-4 mt-2 text-slate-700">
                            <div>
                                <span class="font-bold text-slate-800">Alasan Kategori:</span> 
                                <span class="ml-1 font-semibold text-red-700 bg-red-100/60 px-2 py-0.5 rounded">{{ $booking->cancellation_reason ?: '-' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800">Dibatalkan Oleh:</span> 
                                <span class="ml-1 font-semibold text-slate-800">{{ $booking->cancelledByUser ? $booking->cancelledByUser->name : 'Sistem / Otomatis' }}</span>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="font-bold text-slate-800">Catatan Detail:</span> 
                                <p class="mt-1 bg-white border border-slate-200 rounded-lg p-2.5 text-xs text-slate-600 font-medium italic">"{{ $booking->cancellation_note ?: 'Tidak ada catatan.' }}"</p>
                            </div>
                            <div>
                                <span class="font-bold text-slate-800">Waktu Pembatalan:</span> 
                                <span class="ml-1 font-semibold text-slate-800">{{ $booking->cancelled_at ? $booking->cancelled_at->format('d M Y, H:i') . ' WIB' : '-' }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Ruang Meeting</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">
                            @if($booking->room)
                                {{ $booking->room->name }} ({{ $booking->room->location }})
                            @else
                                <span class="text-slate-500 italic">Tidak membutuhkan ruangan fisik (Online)</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Tanggal Pemesanan</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->booking_date->format('d F Y') }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Waktu Jadwal</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">
                            {{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Diajukan Oleh</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->user->name }} ({{ $booking->user->email }})</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Diajukan Pada</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->created_at->format('d M Y, H:i') }}</dd>
                    </div>
                    
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Jenis Pertemuan</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-bold uppercase">{{ $booking->meeting_type }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-semibold text-[#475569]">Jumlah Peserta</dt>
                        <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->participant_count }} orang</dd>
                    </div>

                    @if($booking->meeting_type === 'offline')
                        @if($booking->additional_requirements)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-semibold text-[#475569]">Kebutuhan Tambahan (Disediakan oleh GA)</dt>
                                <dd class="mt-1.5 flex flex-wrap gap-1.5">
                                    @foreach($booking->additional_requirements as $req)
                                        <span class="inline-flex items-center rounded bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            {{ $req }}
                                        </span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                    @else
                        <div class="sm:col-span-1">
                            <dt class="text-sm font-semibold text-[#475569]">Platform Meeting</dt>
                            <dd class="mt-1 text-sm text-[#0F172A] font-medium">{{ $booking->online_platform ?: 'Microsoft Teams' }}</dd>
                        </div>
                    @endif

                    @if(in_array($booking->status, ['approved', 'completed', 'cancelled']))
                        <div class="sm:col-span-2 border-t border-[#E5E7EB] pt-4">
                            <h4 class="text-sm font-semibold text-[#475569] mb-2">Persiapan Layanan</h4>
                            
                            @if($booking->ga_status !== null || $booking->it_status !== null)
                                <!-- New Revisions flow -->
                                <div class="space-y-4">
                                    @if($booking->ga_status !== null)
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                            <span class="text-xs font-semibold text-[#475569] w-48">Persiapan Ruangan (GA):</span>
                                            <div class="flex items-center gap-2">
                                                @php
                                                    $gaStyles = [
                                                        'pending' => 'background-color: #FFFBEB; color: #D97706; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.2);',
                                                        'in_progress' => 'background-color: #EEF2FF; color: #4F46E5; box-shadow: inset 0 0 0 1px rgba(79, 70, 229, 0.2);',
                                                        'ready' => 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);',
                                                        'not_ready' => 'background-color: #FEF2F2; color: #DC2626; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.2);',
                                                    ];
                                                    $gaLabels = [
                                                        'pending' => 'Menunggu Persiapan',
                                                        'in_progress' => 'Sedang Dipersiapkan',
                                                        'ready' => 'Siap',
                                                        'not_ready' => 'Tidak Siap',
                                                    ];
                                                    $gaStyle = $gaStyles[$booking->ga_status] ?? $gaStyles['pending'];
                                                    $gaLabel = $gaLabels[$booking->ga_status] ?? 'Menunggu Persiapan';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold" style="{{ $gaStyle }}">
                                                    {{ $gaLabel }}
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    (dikerjakan oleh: {{ $booking->preparedByGa ? $booking->preparedByGa->name : 'belum ada staff yang mengambil' }})
                                                </span>
                                            </div>
                                        </div>
                                    @endif

                                    @if($booking->it_status !== null)
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                                            <span class="text-xs font-semibold text-[#475569] w-48">Persiapan Teknis (IT):</span>
                                            <div class="flex items-center gap-2">
                                                @php
                                                    $itStyles = [
                                                        'pending' => 'background-color: #FFFBEB; color: #D97706; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.2);',
                                                        'in_progress' => 'background-color: #EEF2FF; color: #4F46E5; box-shadow: inset 0 0 0 1px rgba(79, 70, 229, 0.2);',
                                                        'ready' => 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);',
                                                        'not_ready' => 'background-color: #FEF2F2; color: #DC2626; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.2);',
                                                    ];
                                                    $itLabels = [
                                                        'pending' => 'Menunggu Persiapan',
                                                        'in_progress' => 'Sedang Dipersiapkan',
                                                        'ready' => 'Siap',
                                                        'not_ready' => 'Tidak Siap',
                                                    ];
                                                    $itStyle = $itStyles[$booking->it_status] ?? $itStyles['pending'];
                                                    $itLabel = $itLabels[$booking->it_status] ?? 'Menunggu Persiapan';
                                                @endphp
                                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold" style="{{ $itStyle }}">
                                                    {{ $itLabel }}
                                                </span>
                                                <span class="text-xs text-slate-500">
                                                    (dikerjakan oleh: {{ $booking->preparedByIt ? $booking->preparedByIt->name : 'belum ada staff yang mengambil' }})
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <!-- Legacy flow -->
                                <div class="flex items-center gap-3 mt-1">
                                    @php
                                        $prepStyles = [
                                            'Menunggu Persiapan' => 'background-color: #FFFBEB; color: #D97706; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.2);',
                                            'Sedang Dipersiapkan' => 'background-color: #EEF2FF; color: #4F46E5; box-shadow: inset 0 0 0 1px rgba(79, 70, 229, 0.2);',
                                            'Siap' => 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);',
                                        ];
                                        $prepStyle = $prepStyles[$booking->preparation_status] ?? $prepStyles['Menunggu Persiapan'];
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold" style="{{ $prepStyle }}">
                                        {{ $booking->preparation_status ?: 'Menunggu Persiapan' }}
                                    </span>

                                </div>
                            @endif
                        </div>
                    @endif
                    
                    @if ($booking->description)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-semibold text-[#475569]">Agenda / Tujuan</dt>
                            <dd class="mt-1 text-sm text-[#0F172A] whitespace-pre-line">{{ $booking->description }}</dd>
                        </div>
                    @endif
                    
                    @if ($booking->status === 'approved' && $booking->approver)
                        <div class="sm:col-span-2 border-t border-[#E5E7EB] pt-4">
                            <h4 class="text-sm font-semibold text-[#475569] mb-1">Informasi Persetujuan</h4>
                            <p class="text-sm text-[#0F172A]">
                                Disetujui oleh <span class="font-medium">{{ $booking->approver->name }}</span>
                                @if ($booking->approved_at)
                                    pada {{ $booking->approved_at->format('d M Y, H:i') }}
                                @endif
                            </p>
                        </div>
                    @elseif ($booking->status === 'rejected')
                        <div class="sm:col-span-2 border-t border-[#E5E7EB] pt-4">
                            <h4 class="text-sm font-semibold text-red-800 mb-1">Detail Penolakan</h4>
                            <p class="text-sm text-[#0F172A]"><span class="font-semibold text-[#475569]">Alasan:</span> {{ $booking->rejection_reason ?: 'Tidak ada alasan yang diberikan.' }}</p>
                        </div>
                    @endif
                </dl>
            </div>

            @can('cancel', $booking)
                <div class="bg-[#F9FAFB] px-6 py-4 border-t border-[#E5E7EB] flex justify-end">
                    {{-- Trigger confirmation modal, do NOT submit form directly --}}
                    <button type="button" onclick="document.getElementById('cancelBookingModal').classList.remove('hidden')"
                        class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        Batalkan Booking
                    </button>
                </div>

                {{-- Confirmation Modal --}}
                <div id="cancelBookingModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4">
                        <div class="text-center">
                            <span class="material-symbols-outlined text-[40px] text-amber-500 mb-2">warning</span>
                            <h3 class="text-lg font-bold text-[#0F172A]">Batalkan Booking?</h3>
                            <p class="text-sm text-slate-500 mt-2">Apakah Anda yakin ingin membatalkan booking ini? Booking yang telah dibatalkan tidak dapat dipulihkan.</p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 pt-2">
                            <button type="button" onclick="document.getElementById('cancelBookingModal').classList.add('hidden')"
                                class="flex-1 bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 rounded-lg hover:bg-slate-200 transition-colors">
                                Kembali
                            </button>
                            <form action="{{ route('bookings.cancel', $booking) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 text-white text-sm font-bold py-2.5 rounded-lg hover:bg-red-700 transition-colors">
                                    Ya, Batalkan Booking
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
        </div>

        @if ($booking->status === 'completed')
            @if ($booking->rating !== null)
                @if (auth()->user()->isAdmin() || $booking->user_id === auth()->id())
                    <!-- Display Review -->
                    <div class="mt-8 bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm">
                        <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider mb-3">Feedback & Rating Pengguna</h3>
                        <div class="flex items-center gap-1 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <span class="material-symbols-outlined text-[20px] {{ $i <= $booking->rating ? 'text-amber-400' : 'text-slate-300' }}" {!! $i <= $booking->rating ? 'style="font-variation-settings: \'FILL\' 1;"' : '' !!}>star</span>
                            @endfor
                        </div>
                        @if($booking->feedback)
                            <p class="text-sm text-slate-600 italic">"{{ $booking->feedback }}"</p>
                        @else
                            <p class="text-xs text-slate-400 italic">Tidak ada feedback tertulis yang diberikan.</p>
                        @endif
                    </div>
                @endif
            @elseif ($booking->user_id === auth()->id())
                <!-- Review Form -->
                <div class="mt-8 bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm">
                    <h3 class="text-lg font-bold text-[#0F172A] mb-1">Bagaimana pengalaman ruang meeting Anda?</h3>
                    <p class="text-xs text-slate-500 mb-4">Harap beri rating kondisi ruangan dan bagikan feedback Anda.</p>
                    
                    <form action="{{ route('bookings.review.store', $booking) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center gap-1" id="star-rating-container">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" onclick="selectStars({{ $i }})" class="star-btn focus:outline-none" data-value="{{ $i }}">
                                    <span class="material-symbols-outlined text-[32px] text-slate-300 hover:text-amber-400 transition-colors" id="star-{{ $i }}">star</span>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="rating_value" value="" required>
                        
                        <div>
                            <label for="feedback" class="block text-xs font-semibold text-slate-600 mb-1">Tulis feedback Anda...</label>
                            <textarea name="feedback" id="feedback" rows="3" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-[#2563EB] focus:outline-none" placeholder="Bagikan pengalaman Anda (agenda, kebersihan, fasilitas ruangan, dll.)..."></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-[#2563EB] text-white rounded px-4 py-2 text-sm font-semibold hover:bg-[#1D4ED8] transition-colors">
                                Kirim Ulasan
                            </button>
                        </div>
                    </form>
                </div>
                
                <script>
                    function selectStars(count) {
                        document.getElementById('rating_value').value = count;
                        for (let i = 1; i <= 5; i++) {
                            const star = document.getElementById('star-' + i);
                            if (i <= count) {
                                star.classList.remove('text-slate-300');
                                star.classList.add('text-amber-400');
                                star.style.fontVariationSettings = "'FILL' 1";
                            } else {
                                star.classList.remove('text-amber-400');
                                star.classList.add('text-slate-300');
                                star.style.fontVariationSettings = "'FILL' 0";
                            }
                        }
                    }
                </script>
            @endif
        @endif
    </main>
</div>
@endsection
