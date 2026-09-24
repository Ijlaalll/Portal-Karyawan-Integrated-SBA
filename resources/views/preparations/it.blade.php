@extends('layouts.app')

@section('title', 'Tugas Persiapan IT - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <!-- Header -->
    <header class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">Tugas Persiapan Meeting (IT)</h1>
            <p class="text-sm text-[#475569] mt-1">Siapkan peralatan audio-visual dan konektivitas untuk mendukung jalannya pertemuan hybrid secara lancar.</p>
        </div>
    </header>

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

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 lg:gap-5 mb-8">
        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #2563EB; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Tugas</span>
            <span class="text-2xl font-extrabold text-slate-900 mt-2 block">{{ $stats['total'] }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #D97706; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider block">Menunggu</span>
            <span class="text-2xl font-extrabold text-amber-600 mt-2 block">{{ $stats['pending'] }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #4F46E5; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block">Sedang Dikerjakan</span>
            <span class="text-2xl font-extrabold text-indigo-600 mt-2 block">{{ $stats['in_progress'] }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #059669; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider block">Tugas Siap</span>
            <span class="text-2xl font-extrabold text-emerald-600 mt-2 block">{{ $stats['ready'] }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #DC2626; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider block">Tugas Tidak Siap</span>
            <span class="text-2xl font-extrabold text-rose-600 mt-2 block">{{ $stats['not_ready'] }}</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm transition-all overflow-hidden flex flex-col justify-between" 
            style="border: 1px solid #E5E7EB; border-left: 4px solid #475569; padding: 14px 14px 14px 20px;">
            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tugas Selesai</span>
            <span class="text-2xl font-extrabold text-slate-800 mt-2 block">{{ $stats['completed'] }}</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex" style="margin-bottom: -1px;" aria-label="Tabs">
            <a href="{{ route('it.preparations', ['status' => 'active']) }}" 
               class="text-sm font-bold transition-all"
               style="margin-right: 24px; padding-bottom: 12px; padding-top: 12px; padding-left: 4px; padding-right: 4px; border-bottom: 2px solid {{ $status === 'active' ? '#2563EB' : 'transparent' }}; color: {{ $status === 'active' ? '#2563EB' : '#64748B' }};">
                Tugas Aktif
            </a>
            <a href="{{ route('it.preparations', ['status' => 'history']) }}" 
               class="text-sm font-bold transition-all"
               style="padding-bottom: 12px; padding-top: 12px; padding-left: 4px; padding-right: 4px; border-bottom: 2px solid {{ $status === 'history' ? '#2563EB' : 'transparent' }}; color: {{ $status === 'history' ? '#2563EB' : '#64748B' }};">
                Riwayat Tugas
            </a>
        </nav>
    </div>

    <!-- Tasks Grid -->
    <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
        @if($bookings->isEmpty())
            <div class="px-6 py-16 text-center text-[#475569] italic bg-white">
                <span class="material-symbols-outlined text-[48px] text-gray-300 mb-2">playlist_add_check</span>
                <p>Tidak ada tugas online meeting {{ $status === 'active' ? 'aktif' : 'riwayat' }} saat ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB] text-left">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-semibold text-[#475569] uppercase tracking-wider">Detail Pemesanan</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu & Jadwal</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-[#475569] uppercase tracking-wider">Peserta & Platform</th>
                        <th class="px-6 py-3.5 text-xs font-semibold text-[#475569] uppercase tracking-wider">Status & Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                    @foreach($bookings as $booking)
                        <tr class="align-top hover:bg-slate-50/40">
                            <!-- Booking title & requester -->
                            <td class="px-6 py-4">
                                <span class="font-bold text-[#0F172A] block">{{ $booking->title }}</span>
                                <span class="text-xs text-slate-500 block mt-1">Oleh: {{ $booking->user->name }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $booking->user->email }}</span>
                                @if($booking->description)
                                    <span class="text-xs text-slate-500 block mt-2 max-w-[200px] truncate" title="{{ $booking->description }}">
                                        Agenda: {{ $booking->description }}
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Schedule details -->
                            <td class="px-6 py-4">
                                <span class="font-semibold block text-slate-800">{{ $booking->booking_date->format('d M Y') }}</span>
                                <span class="text-slate-500 text-xs">{{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}</span>
                            </td>
                            
                            <!-- Participant details & platform -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1 text-xs font-semibold text-slate-700">
                                    <span class="material-symbols-outlined text-[16px]">groups</span>
                                    <span>{{ $booking->participant_count }} orang</span>
                                </div>
                                <span class="inline-flex items-center rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-800 ring-1 ring-inset ring-emerald-600/10 mt-2">
                                    {{ $booking->online_platform ?: 'Microsoft Teams' }}
                                </span>
                            </td>
                            
                            <!-- Status action -->
                            <td class="px-6 py-4">
                                 @if($status === 'active')
                                     <form action="{{ route('preparations.status.update', $booking) }}" method="POST" class="flex flex-col gap-2 max-w-[200px]">
                                         @csrf
                                         
                                         @if($booking->it_status !== null)
                                             @php
                                                 $currentStyle = $booking->it_status === 'in_progress' 
                                                     ? 'background-color: #EEF2FF; color: #4F46E5; border-color: rgba(79, 70, 229, 0.3);' 
                                                     : ($booking->it_status === 'ready' 
                                                         ? 'background-color: #ECFDF5; color: #059669; border-color: rgba(5, 150, 105, 0.3);'
                                                         : ($booking->it_status === 'not_ready'
                                                             ? 'background-color: #FEF2F2; color: #DC2626; border-color: rgba(220, 38, 38, 0.3);'
                                                             : 'background-color: #FFFBEB; color: #D97706; border-color: rgba(217, 119, 6, 0.3);'));
                                             @endphp
                                         <select name="preparation_status" id="it_status_select_{{ $booking->id }}" required
                                                 onchange="itUpdateSelectStyle(this, {{ $booking->id }})"
                                                 class="rounded border px-2.5 py-1.5 text-xs font-bold focus:outline-none transition-all cursor-pointer"
                                                 style="{{ $currentStyle }}">
                                             <option value="pending" {{ $booking->it_status === 'pending' ? 'selected' : '' }}>Menunggu Persiapan</option>
                                             <option value="in_progress" {{ $booking->it_status === 'in_progress' ? 'selected' : '' }}>Sedang Dipersiapkan</option>
                                             <option value="ready" {{ $booking->it_status === 'ready' ? 'selected' : '' }}>Siap</option>
                                             <option value="not_ready" {{ $booking->it_status === 'not_ready' ? 'selected' : '' }}>Tidak Siap</option>
                                         </select>
                                         <input type="hidden" name="cancellation_note" value="">
                                     @else
                                         @php
                                             $currentStyle = $booking->preparation_status === 'Sedang Dipersiapkan' 
                                                 ? 'background-color: #EEF2FF; color: #4F46E5; border-color: rgba(79, 70, 229, 0.3);' 
                                                 : ($booking->preparation_status === 'Siap' 
                                                     ? 'background-color: #ECFDF5; color: #059669; border-color: rgba(5, 150, 105, 0.3);'
                                                     : 'background-color: #FFFBEB; color: #D97706; border-color: rgba(217, 119, 6, 0.3);');
                                         @endphp
                                         <select name="preparation_status" id="prep_status_{{ $booking->id }}" required onchange="updateSelectStyle(this)"
                                                 class="rounded border px-2.5 py-1.5 text-xs font-bold focus:outline-none transition-all cursor-pointer"
                                                 style="{{ $currentStyle }}">
                                             <option value="Menunggu Persiapan" {{ $booking->preparation_status === 'Menunggu Persiapan' ? 'selected' : '' }}>Menunggu Persiapan</option>
                                             <option value="Sedang Dipersiapkan" {{ $booking->preparation_status === 'Sedang Dipersiapkan' ? 'selected' : '' }}>Sedang Dipersiapkan</option>
                                             <option value="Siap" {{ $booking->preparation_status === 'Siap' ? 'selected' : '' }}>Siap</option>
                                         </select>
                                         <input type="hidden" name="cancellation_note" value="">
                                     @endif
                                     
                                     <button type="button"
                                         onclick="itHandleSubmit(this.closest('form'), {{ $booking->id }})"
                                         class="rounded bg-[#2563EB] py-1 px-3 text-xs font-bold text-white hover:bg-[#1D4ED8] transition-colors shadow-xs mt-1">
                                         Simpan
                                     </button>
                                 </form>
                                 @else
                                     @php
                                         if ($booking->it_status !== null) {
                                             $label = $booking->it_status === 'ready' ? 'Siap' : ($booking->it_status === 'not_ready' ? 'Tidak Siap' : $booking->it_status);
                                             $style = $booking->it_status === 'ready' ? 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);' : 'background-color: #FEF2F2; color: #DC2626; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.2);';
                                         } else {
                                             $label = $booking->preparation_status;
                                             $style = $booking->preparation_status === 'Siap' ? 'background-color: #D1FAE5; color: #059669; box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);' : 'background-color: #FEF2F2; color: #DC2626; box-shadow: inset 0 0 0 1px rgba(220, 38, 38, 0.2);';
                                         }
                                     @endphp
                                     <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold" style="{{ $style }}">
                                         {{ $label }}
                                     </span>
                                     @if($booking->it_status === 'not_ready' || ($booking->it_status === null && $booking->preparation_status === 'Tidak Siap') || $booking->status === 'cancelled')
                                         <span class="text-[10px] text-red-600 block mt-1 font-semibold">Dibatalkan</span>
                                         @if($booking->cancellation_reason)
                                             <span class="text-[9px] text-slate-500 block">Kategori: {{ $booking->cancellation_reason }}</span>
                                         @endif
                                         @if($booking->cancellation_note)
                                             <span class="text-[9px] text-slate-500 block italic">"{{ $booking->cancellation_note }}"</span>
                                         @endif
                                         @if($booking->cancelled_at)
                                             <span class="text-[9px] text-slate-400 block">{{ $booking->cancelled_at->format('d M Y, H:i') }}</span>
                                         @endif
                                     @else
                                         <span class="text-[10px] text-slate-400 block mt-1">Selesai disiapkan</span>
                                     @endif
                                 @endif
                             </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
        
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB]">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</div>

{{-- IT Tidak Siap Confirmation Modal --}}
<div id="itNotReadyModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white border border-[#E5E7EB] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto p-6 shadow-xl flex flex-col space-y-4">
        <div class="text-center">
            <span class="material-symbols-outlined text-[40px] text-red-500 mb-2">warning</span>
            <h3 class="text-lg font-bold text-[#0F172A]">Batalkan Meeting?</h3>
            <p class="text-sm text-slate-500 mt-2">Anda memilih status "Tidak Siap". Tindakan ini akan membatalkan meeting dan user akan menerima pemberitahuan pembatalan.</p>
        </div>
        <div class="space-y-2">
            <label for="it_cancellation_note_input" class="text-xs font-semibold text-slate-700">Alasan Pembatalan <span class="text-red-600">*</span></label>
            <textarea id="it_cancellation_note_input" rows="3" placeholder="Jelaskan alasan teknis yang menyebabkan meeting tidak dapat berlangsung..."
                class="w-full rounded-lg border border-[#E5E7EB] px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"></textarea>
            <p id="it_note_error" class="text-xs text-red-600 hidden">Alasan pembatalan wajib diisi.</p>
        </div>
        <div class="flex gap-3 pt-1">
            <button type="button" onclick="itCloseNotReadyModal()"
                class="flex-1 bg-slate-100 text-slate-700 text-sm font-semibold py-2.5 rounded-lg hover:bg-slate-200 transition-colors">
                Kembali
            </button>
            <button type="button" onclick="itConfirmNotReady()"
                class="flex-1 bg-red-600 text-white text-sm font-bold py-2.5 rounded-lg hover:bg-red-700 transition-colors">
                Ya, Batalkan Meeting
            </button>
        </div>
    </div>
</div>

<script>
    let itCurrentForm = null;
    let itCurrentBookingId = null;

    function itUpdateSelectStyle(selectElement, bookingId) {
        const status = selectElement.value;
        if (status === 'Menunggu Persiapan' || status === 'pending') {
            selectElement.style.backgroundColor = '#FFFBEB';
            selectElement.style.color = '#D97706';
            selectElement.style.borderColor = 'rgba(217, 119, 6, 0.3)';
        } else if (status === 'Sedang Dipersiapkan' || status === 'in_progress') {
            selectElement.style.backgroundColor = '#EEF2FF';
            selectElement.style.color = '#4F46E5';
            selectElement.style.borderColor = 'rgba(79, 70, 229, 0.3)';
        } else if (status === 'Siap' || status === 'ready') {
            selectElement.style.backgroundColor = '#ECFDF5';
            selectElement.style.color = '#059669';
            selectElement.style.borderColor = 'rgba(5, 150, 105, 0.3)';
        } else if (status === 'not_ready') {
            selectElement.style.backgroundColor = '#FEF2F2';
            selectElement.style.color = '#DC2626';
            selectElement.style.borderColor = 'rgba(220, 38, 38, 0.3)';
        }
    }

    function itHandleSubmit(form, bookingId) {
        const select = form.querySelector('select[name="preparation_status"]');
        if (select && select.value === 'not_ready') {
            itCurrentForm = form;
            itCurrentBookingId = bookingId;
            document.getElementById('it_cancellation_note_input').value = '';
            document.getElementById('it_note_error').classList.add('hidden');
            document.getElementById('itNotReadyModal').classList.remove('hidden');
        } else {
            submitFormWithTurbo(form);
        }
    }

    function itCloseNotReadyModal() {
        document.getElementById('itNotReadyModal').classList.add('hidden');
        if (itCurrentForm && itCurrentBookingId) {
            const select = itCurrentForm.querySelector('select[name="preparation_status"]');
            if (select) {
                select.value = 'pending';
                itUpdateSelectStyle(select, itCurrentBookingId);
            }
        }
        itCurrentForm = null;
        itCurrentBookingId = null;
    }

    function itConfirmNotReady() {
        const noteInput = document.getElementById('it_cancellation_note_input');
        const note = noteInput.value.trim();
        if (!note) {
            document.getElementById('it_note_error').classList.remove('hidden');
            return;
        }
        const noteInput = itCurrentForm.querySelector('input[name="cancellation_note"]');
        if (noteInput) noteInput.value = note;
        document.getElementById('itNotReadyModal').classList.add('hidden');
        itCurrentForm.submit();
    }



    function updateSelectStyle(selectElement) {
        const status = selectElement.value;
        if (status === 'Menunggu Persiapan' || status === 'pending') {
            selectElement.style.backgroundColor = '#FFFBEB';
            selectElement.style.color = '#D97706';
            selectElement.style.borderColor = 'rgba(217, 119, 6, 0.3)';
        } else if (status === 'Sedang Dipersiapkan' || status === 'in_progress') {
            selectElement.style.backgroundColor = '#EEF2FF';
            selectElement.style.color = '#4F46E5';
            selectElement.style.borderColor = 'rgba(79, 70, 229, 0.3)';
        } else if (status === 'Siap' || status === 'ready') {
            selectElement.style.backgroundColor = '#ECFDF5';
            selectElement.style.color = '#059669';
            selectElement.style.borderColor = 'rgba(5, 150, 105, 0.3)';
        } else if (status === 'not_ready') {
            selectElement.style.backgroundColor = '#FEF2F2';
            selectElement.style.color = '#DC2626';
            selectElement.style.borderColor = 'rgba(220, 38, 38, 0.3)';
        }
    }
</script>
@endsection
