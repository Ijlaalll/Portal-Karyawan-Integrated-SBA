@extends('layouts.app')

@section('title', 'Review Persetujuan SIK ' . $sik->sik_number . ' - SBA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="{{ route('sik.approvals.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8] inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Antrean Persetujuan
        </a>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
            MENUNGGU PERSETUJUAN
        </span>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8 space-y-6">
        <div class="flex items-center justify-between border-b pb-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Review Pengajuan SIK</h1>
                <p class="text-xs text-slate-500 font-mono mt-0.5">Nomor: {{ $sik->sik_number }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">Tanggal Pengajuan</span>
                <span class="text-sm font-semibold text-slate-800">{{ $sik->created_at->format('d M Y H:i') }} WIB</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Data Pemohon -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Identitas Pemohon</h3>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Nama:</span>
                    <span class="font-bold text-slate-900">{{ $sik->user->name }}</span>
                </div>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Nomor Karyawan:</span>
                    <span class="font-semibold text-slate-900">{{ $sik->employee_id ?: ($sik->user->employee_id ?: '-') }}</span>
                </div>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Departemen:</span>
                    <span class="font-semibold text-slate-900">{{ $sik->department }}</span>
                </div>
                <div class="text-xs flex justify-between py-1">
                    <span class="text-slate-500">Jabatan:</span>
                    <span class="font-semibold text-slate-900">{{ $sik->position ?: '-' }}</span>
                </div>
            </div>

            <!-- Detail Izin -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Waktu & Kendaraan</h3>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Tanggal Izin:</span>
                    <span class="font-bold text-slate-900">{{ $sik->permit_date->format('d M Y') }}</span>
                </div>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Rencana Keluar:</span>
                    <span class="font-bold text-blue-700">{{ $sik->expected_departure_time }} WIB</span>
                </div>
                <div class="text-xs flex justify-between py-1 border-b border-slate-200">
                    <span class="text-slate-500">Rencana Kembali:</span>
                    <span class="font-bold text-blue-700">{{ $sik->expected_return_time }} WIB</span>
                </div>
                <div class="text-xs flex justify-between py-1">
                    <span class="text-slate-500">Kendaraan:</span>
                    <span class="font-semibold text-slate-900">
                        @if($sik->vehicle_type === 'company_vehicle')
                            Dinas SBA ({{ $sik->vehicle_details ?: '-' }})
                        @elseif($sik->vehicle_type === 'private_vehicle')
                            Pribadi ({{ $sik->vehicle_details ?: '-' }})
                        @else
                            Tanpa Kendaraan
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Tujuan & Keperluan</h3>
            <div class="border border-slate-200 p-4 rounded-xl space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $sik->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $sik->purpose_type }}
                    </span>
                    <span class="font-bold text-slate-900 text-sm">{{ $sik->purpose }}</span>
                </div>
                @if($sik->purpose_detail)
                    <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-100">{{ $sik->purpose_detail }}</p>
                @endif
            </div>
        </div>

        <!-- Action Box -->
        <div class="pt-6 border-t flex flex-col sm:flex-row justify-end gap-3">
            <button type="button" onclick="openModal('rejectModal')" class="px-5 py-2.5 rounded-xl border border-red-300 bg-red-50 text-xs font-bold text-red-700 hover:bg-red-100 transition-colors">
                Tolak Pengajuan
            </button>
            <button type="button" onclick="openModal('approveModal')" class="px-6 py-2.5 rounded-xl bg-green-600 text-white text-xs font-bold hover:bg-green-700 transition-colors shadow-sm">
                Setujui Pengajuan (Approve)
            </button>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
            <span class="material-symbols-outlined">check_circle</span>
            Setujui Pengajuan SIK
        </div>
        <p class="text-xs text-slate-600">Apakah Anda yakin menyetujui pengajuan SIK <strong>#{{ $sik->sik_number }}</strong> dari {{ $sik->user->name }}?</p>
        <form action="{{ route('sik.approvals.approve', $sik) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                <textarea name="approval_note" rows="2" placeholder="Tuliskan catatan..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-green-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-xs font-semibold text-white hover:bg-green-700">Setujui SIK</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-red-600 font-bold text-lg">
            <span class="material-symbols-outlined">cancel</span>
            Tolak Pengajuan SIK
        </div>
        <form action="{{ route('sik.approvals.reject', $sik) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                <textarea name="rejection_reason" required rows="3" placeholder="Tuliskan alasan penolakan..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-xs font-semibold text-white hover:bg-red-700">Tolak SIK</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
@endsection
