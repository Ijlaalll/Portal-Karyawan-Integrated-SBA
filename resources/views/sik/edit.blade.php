@extends('layouts.app')

@section('title', 'Edit Draft SIK - Surat Izin Keluar SBA')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <header class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Edit Draft SIK ({{ $sik->sik_number }})
            </h1>
            <p class="text-sm text-[#475569] mt-1">Perbarui data permohonan sebelum dikirim untuk persetujuan HOD.</p>
        </div>
        <a href="{{ route('sik.show', $sik) }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8] inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Detail SIK
        </a>
    </header>

    <main>
        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200 shadow-sm">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-600 text-[22px] mt-0.5">error</span>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">Mohon perbaiki kesalahan berikut:</h3>
                        <ul class="mt-1 text-xs text-red-700 list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('sik.update', $sik) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Snapshot Data Pemohon -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4 pb-3 border-b border-[#F1F5F9]">
                    <span class="material-symbols-outlined text-[#2563EB] text-[22px]">badge</span>
                    <h2 class="text-base font-bold text-[#0F172A]">Data Identitas Pemohon</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="min-w-0">
                        <span class="text-xs text-[#64748B] block">Nama Pemohon</span>
                        <span class="text-sm font-bold text-[#0F172A] truncate block" title="{{ $sik->user->name }}">{{ $sik->user->name }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs text-[#64748B] block">Nomor Karyawan</span>
                        <span class="text-sm font-semibold text-[#0F172A] truncate block">{{ $sik->employee_id ?: ($sik->user->employee_id ?: '-') }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs text-[#64748B] block">Departemen</span>
                        <span class="text-sm font-semibold text-[#0F172A] truncate block" title="{{ $sik->department ?: ($sik->user->department ?: '-') }}">{{ $sik->department ?: ($sik->user->department ?: '-') }}</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-xs text-[#64748B] block">Jabatan / Posisi</span>
                        <span class="text-sm font-semibold text-[#0F172A] truncate block" title="{{ $sik->position ?: ($sik->user->position ?: '-') }}">{{ $sik->position ?: ($sik->user->position ?: '-') }}</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Keperluan & Waktu -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-5">
                <div class="flex items-center gap-2 pb-3 border-b border-[#F1F5F9]">
                    <span class="material-symbols-outlined text-[#2563EB] text-[22px]">schedule</span>
                    <h2 class="text-base font-bold text-[#0F172A]">Rincian Keperluan & Waktu</h2>
                </div>

                <!-- Jenis Keperluan -->
                <div>
                    <label class="block text-sm font-semibold text-[#0F172A] mb-2">Jenis Keperluan <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="relative flex items-center p-3 rounded-lg border cursor-pointer hover:bg-slate-50 transition-colors {{ old('purpose_type', $sik->purpose_type) === 'dinas' ? 'border-[#2563EB] bg-blue-50/40 ring-1 ring-[#2563EB]' : 'border-[#E5E7EB]' }}">
                            <input type="radio" name="purpose_type" value="dinas" {{ old('purpose_type', $sik->purpose_type) === 'dinas' ? 'checked' : '' }} class="h-4 w-4 text-[#2563EB] focus:ring-[#2563EB]">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-[#0F172A]">Dinas / Tugas Pekerjaan</span>
                                <span class="block text-xs text-[#64748B]">Urusan kedinasan resmi perusahaan SBA</span>
                            </div>
                        </label>

                        <label class="relative flex items-center p-3 rounded-lg border cursor-pointer hover:bg-slate-50 transition-colors {{ old('purpose_type', $sik->purpose_type) === 'pribadi' ? 'border-purple-600 bg-purple-50/40 ring-1 ring-purple-600' : 'border-[#E5E7EB]' }}">
                            <input type="radio" name="purpose_type" value="pribadi" {{ old('purpose_type', $sik->purpose_type) === 'pribadi' ? 'checked' : '' }} class="h-4 w-4 text-purple-600 focus:ring-purple-600">
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-[#0F172A]">Pribadi / Urusan Keluarga</span>
                                <span class="block text-xs text-[#64748B]">Keperluan pribadi mendesak atau izin istirahat</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Keperluan Ringkas -->
                <div>
                    <label for="purpose" class="block text-sm font-semibold text-[#0F172A]">
                        Keperluan / Tujuan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="purpose" id="purpose" required value="{{ old('purpose', $sik->purpose) }}" placeholder="mis. Koordinasi lapangan vendor di Pelabuhan Malahayati"
                        class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3.5 py-2.5 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                </div>

                <!-- Detail Keperluan -->
                <div>
                    <label for="purpose_detail" class="block text-sm font-semibold text-[#0F172A]">
                        Detail Keterangan Keperluan
                    </label>
                    <textarea name="purpose_detail" id="purpose_detail" rows="3" placeholder="Tuliskan keterangan tambahan atau rincian agenda jika diperlukan..."
                        class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3.5 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">{{ old('purpose_detail', $sik->purpose_detail) }}</textarea>
                </div>

                <!-- Waktu Keluar & Rencana Kembali -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label for="permit_date" class="block text-sm font-semibold text-[#0F172A]">
                            Tanggal Izin <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="permit_date" id="permit_date" required value="{{ old('permit_date', $sik->permit_date->format('Y-m-d')) }}"
                            class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label for="expected_departure_time" class="block text-sm font-semibold text-[#0F172A]">
                            Rencana Waktu Keluar <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="expected_departure_time" id="expected_departure_time" required value="{{ old('expected_departure_time', $sik->expected_departure_time) }}"
                            class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    </div>

                    <div>
                        <label for="expected_return_time" class="block text-sm font-semibold text-[#0F172A]">
                            Rencana Waktu Kembali <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="expected_return_time" id="expected_return_time" required value="{{ old('expected_return_time', $sik->expected_return_time) }}"
                            class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                </div>

                <!-- Kendaraan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="vehicle_type" class="block text-sm font-semibold text-[#0F172A]">
                            Kendaraan
                        </label>
                        <select name="vehicle_type" id="vehicle_type" class="mt-1 block w-full rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                            <option value="none" {{ old('vehicle_type', $sik->vehicle_type) === 'none' ? 'selected' : '' }}>Tanpa Kendaraan / Jalan Kaki</option>
                            <option value="company_vehicle" {{ old('vehicle_type', $sik->vehicle_type) === 'company_vehicle' ? 'selected' : '' }}>Kendaraan Dinas / Operasional SBA</option>
                            <option value="private_vehicle" {{ old('vehicle_type', $sik->vehicle_type) === 'private_vehicle' ? 'selected' : '' }}>Kendaraan Pribadi</option>
                        </select>
                    </div>

                    <div>
                        <label for="vehicle_details" class="block text-sm font-semibold text-[#0F172A]">
                            Nomor Polisi / Identitas Kendaraan
                        </label>
                        <input type="text" name="vehicle_details" id="vehicle_details" value="{{ old('vehicle_details', $sik->vehicle_details) }}" placeholder="mis. BL 1234 AB / Avanza Hitam"
                            class="mt-1 block w-full rounded-lg border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                    </div>
                </div>
            </div>

            <!-- Section 3: Pilihan Pejabat Pemberi Persetujuan (Approver) -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-[#F1F5F9]">
                    <span class="material-symbols-outlined text-[#2563EB] text-[22px]">how_to_reg</span>
                    <h2 class="text-base font-bold text-[#0F172A]">Pejabat Pemberi Persetujuan (Approver)</h2>
                </div>

                <p class="text-xs text-slate-500">
                    Pilih pejabat yang akan memverifikasi dan menyetujui surat izin keluar Anda. Secara default, izin akan diajukan ke Default Approver departemen Anda.
                </p>

                @php
                    $isRep = old('approver_type', $sik->is_replacement_approver ? 'replacement' : 'default') === 'replacement';
                @endphp

                <div class="space-y-3">
                    <!-- Option A: Default Approver -->
                    <label class="flex items-start p-3.5 rounded-xl border cursor-pointer hover:bg-slate-50 transition-colors {{ !$isRep ? 'border-[#2563EB] bg-blue-50/30 ring-1 ring-[#2563EB]' : 'border-slate-200' }}">
                        <input type="radio" name="approver_type" value="default" id="radioDefaultApprover"
                            {{ !$isRep ? 'checked' : '' }}
                            onchange="toggleApproverSelection()"
                            class="mt-1 h-4 w-4 text-[#2563EB] focus:ring-[#2563EB]">
                        <div class="ml-3 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-[#0F172A]">Default Approver Departemen</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-blue-800">Rekomendasi</span>
                            </div>
                            <div class="mt-1 text-xs text-slate-700">
                                @if(isset($approvalOptions['default_approver']))
                                    <span class="font-semibold">{{ $approvalOptions['default_approver']->name }}</span>
                                    <span class="text-slate-500">• {{ $approvalOptions['default_approver']->position ?: 'Head of Department' }} ({{ $approvalOptions['default_approver']->email }})</span>
                                @else
                                    <span class="text-amber-600 italic">Konfigurasi HOD belum tersedia.</span>
                                @endif
                            </div>
                        </div>
                    </label>

                    <!-- Option B: Approver Pengganti -->
                    <label class="flex items-start p-3.5 rounded-xl border cursor-pointer hover:bg-slate-50 transition-colors {{ $isRep ? 'border-purple-600 bg-purple-50/30 ring-1 ring-purple-600' : 'border-slate-200' }}">
                        <input type="radio" name="approver_type" value="replacement" id="radioReplacementApprover"
                            {{ $isRep ? 'checked' : '' }}
                            onchange="toggleApproverSelection()"
                            class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-600">
                        <div class="ml-3 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-[#0F172A]">Approver Pengganti (Authorized Alternative)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800">Pilihan Lain</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Gunakan jika Default Approver sedang berhalangan hadir / cuti. Hanya pejabat yang telah diotorisasi dalam Approval Setting yang dapat dipilih.
                            </p>

                            <div id="replacementDropdownContainer" class="mt-3 {{ $isRep ? '' : 'hidden' }}">
                                @if(isset($approvalOptions['replacement_approvers']) && $approvalOptions['replacement_approvers']->count() > 0)
                                    <select name="replacement_approver_id" id="replacement_approver_id"
                                        class="block w-full rounded-lg border border-[#E5E7EB] bg-white px-3 py-2 text-xs text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]">
                                        <option value="">-- Pilih Approver Pengganti Resmi --</option>
                                        @foreach($approvalOptions['replacement_approvers'] as $rep)
                                            <option value="{{ $rep->id }}" {{ old('replacement_approver_id', $sik->approver_id) == $rep->id ? 'selected' : '' }}>
                                                {{ $rep->name }} ({{ $rep->department }} - {{ $rep->position ?: 'Staff' }})
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                                        Tidak ada Approver Pengganti yang dikonfigurasi untuk departemen Anda. Pengajuan akan diproses oleh Default Approver.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-3 pt-4 border-t border-[#E5E7EB]">
                <a href="{{ route('sik.show', $sik) }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </a>

                <button type="submit" name="action" value="draft" class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Simpan Perubahan DRAFT
                </button>

                <button type="submit" name="action" value="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#2563EB] hover:bg-[#1D4ED8] shadow-sm transition-colors inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Simpan & Kirim Pengajuan
                </button>
            </div>
        </form>
    </main>
</div>

<script>
    function toggleApproverSelection() {
        const isReplacement = document.getElementById('radioReplacementApprover').checked;
        const container = document.getElementById('replacementDropdownContainer');
        if (container) {
            if (isReplacement) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    }
</script>
@endsection
