@extends('layouts.app')

@section('title', 'Detail SIK ' . $sik->sik_number . ' - SBA')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Action Nav -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <a href="{{ route('sik.index') }}" class="text-sm font-semibold text-[#2563EB] hover:text-[#1D4ED8] inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Daftar SIK
        </a>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('sik.print', $sik) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak Surat Fisik
            </a>

            @if($sik->isDraft() && $sik->user_id === auth()->id())
                <a href="{{ route('sik.edit', $sik) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    Edit Draft
                </a>
                <form action="{{ route('sik.submit', $sik) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">send</span>
                        Kirim Pengajuan
                    </button>
                </form>
            @endif

            @if($sik->canBeCancelledBy(auth()->user()))
                <form action="{{ route('sik.cancel', $sik) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan SIK ini?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-red-300 bg-red-50 text-xs font-semibold text-red-700 hover:bg-red-100 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                        Batalkan Pengajuan
                    </button>
                </form>
            @endif

            @can('approve', $sik)
                <button type="button" onclick="openModal('approveModal')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    Setujui (Approve)
                </button>
            @endcan

            @can('reject', $sik)
                <button type="button" onclick="openModal('rejectModal')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">cancel</span>
                    Tolak (Reject)
                </button>
            @endcan

            @can('revoke', $sik)
                <button type="button" onclick="openModal('revokeModal')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-red-300 bg-red-50 text-xs font-semibold text-red-700 hover:bg-red-100 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">block</span>
                    Cabut Izin (Revoke)
                </button>
            @endcan

            @can('closeManually', $sik)
                <button type="button" onclick="openModal('closeManualModal')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-orange-300 bg-orange-50 text-xs font-semibold text-orange-800 hover:bg-orange-100 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                    Tutup Manual (Emergency)
                </button>
            @endcan
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="rounded-xl bg-green-50 p-4 border border-green-200 shadow-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-green-600 text-[22px]">check_circle</span>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-red-50 p-4 border border-red-200 shadow-sm flex items-center gap-3">
            <span class="material-symbols-outlined text-red-600 text-[22px]">error</span>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white border border-[#E5E7EB] rounded-2xl shadow-sm overflow-hidden">
        <!-- Letterhead -->
        <div class="p-6 sm:p-8 bg-[#0F172A] text-white flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-black text-xl text-blue-400 tracking-wider">PT SOLUSI BANGUN ANDALAS</span>
                </div>
                <h1 class="text-2xl font-bold text-white mt-1">SURAT IZIN KELUAR (SIK)</h1>
                <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $sik->sik_number }}</p>
            </div>

            <div class="text-right sm:self-center">
                @php
                    $effStatus = $sik->effective_status;
                @endphp
                @if($effStatus === 'draft')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-700 text-slate-200 border border-slate-600">
                        DRAFT
                    </span>
                @elseif($effStatus === 'pending_approval')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                        MENUNGGU APPROVAL
                    </span>
                @elseif($effStatus === 'approved')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        DISETUJUI / SIAP KELUAR
                    </span>
                @elseif($effStatus === 'outside')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        SEDANG DI LUAR PABRIK
                    </span>
                @elseif($effStatus === 'overdue')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-500/30 text-rose-300 border border-rose-500/50 animate-pulse">
                        TERLAMBAT KEMBALI (OVERDUE)
                    </span>
                @elseif($effStatus === 'returned')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-green-500/20 text-green-300 border border-green-500/30">
                        SUDAH KEMBALI
                    </span>
                @elseif($effStatus === 'closed_manually')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-orange-500/20 text-orange-300 border border-orange-500/30">
                        DITUTUP SECARA MANUAL
                    </span>
                @elseif($effStatus === 'revoked')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                        IZIN DICABUT (REVOKED)
                    </span>
                @elseif($effStatus === 'rejected')
                    <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                        DITOLAK (REJECTED)
                    </span>
                @endif
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">
            <!-- Grid: Applicant Snapshot & QR Code (if approved) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Details -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Data Pemohon -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Data Pemohon (Snapshot)</h3>
                        <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <span class="text-xs text-slate-500 block">Nama Karyawan</span>
                                <span class="text-sm font-bold text-slate-900">{{ $sik->user->name }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Nomor Karyawan</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $sik->employee_id ?: ($sik->user->employee_id ?: '-') }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Departemen</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $sik->department }}</span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Jabatan / Posisi</span>
                                <span class="text-sm font-semibold text-slate-900">{{ $sik->position ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Keperluan & Waktu -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Detail Keperluan & Waktu</h3>
                        <div class="border border-slate-200 rounded-xl p-4 space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <span class="text-xs text-slate-500 block">Jenis Keperluan</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold uppercase tracking-wider {{ $sik->purpose_type === 'dinas' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $sik->purpose_type }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-500 block">Tanggal Izin</span>
                                    <span class="text-sm font-bold text-slate-900">{{ $sik->permit_date->format('d F Y') }}</span>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-500 block">Keperluan / Agenda</span>
                                <p class="text-sm font-bold text-slate-900 mt-0.5">{{ $sik->purpose }}</p>
                                @if($sik->purpose_detail)
                                    <p class="text-xs text-slate-600 mt-1 whitespace-pre-line bg-slate-50 p-2.5 rounded-lg border border-slate-100">{{ $sik->purpose_detail }}</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                                <div>
                                    <span class="text-xs text-slate-500 block">Rencana Keluar</span>
                                    <span class="text-sm font-bold text-blue-700">{{ $sik->expected_departure_time }} WIB</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 block">Rencana Kembali</span>
                                    <span class="text-sm font-bold text-blue-700">{{ $sik->expected_return_time }} WIB</span>
                                </div>
                                <div>
                                    <span class="text-xs text-slate-500 block">Kendaraan</span>
                                    <span class="text-sm font-semibold text-slate-800">
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
                    </div>
                </div>

                <!-- Right Col: QR Code Card (Only for APPROVED / OUTSIDE / OVERDUE) -->
                <div class="flex flex-col items-center justify-center p-6 bg-slate-50 border border-slate-200 rounded-xl text-center">
                    @if($sik->isApproved() || $sik->isOutside() || $sik->isOverdue())
                        <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-200 mb-3">
                            {!! app(\App\Services\QrCodeService::class)->generateSvg($sik->qr_token, 180) !!}
                        </div>
                        <p class="text-xs font-bold text-slate-800">QR Code Validasi Security</p>
                        <p class="text-[10px] text-slate-500 font-mono mt-1 break-all px-2">{{ $sik->qr_token }}</p>
                        <p class="text-[11px] text-blue-600 font-semibold mt-2">Tunjukkan QR Code ini kepada Security di pos gerbang saat keluar & kembali.</p>
                    @elseif($sik->isReturned())
                        <div class="p-6 bg-green-50 rounded-2xl border border-green-200 text-green-700 mb-2">
                            <span class="material-symbols-outlined text-5xl">task_alt</span>
                        </div>
                        <p class="text-sm font-bold text-green-800">SIK Selesai</p>
                        <p class="text-xs text-green-600 mt-1">Pemohon telah kembali dan izin ini telah diselesaikan.</p>
                    @elseif($sik->isClosedManually())
                        <div class="p-6 bg-orange-50 rounded-2xl border border-orange-200 text-orange-700 mb-2">
                            <span class="material-symbols-outlined text-5xl">lock</span>
                        </div>
                        <p class="text-sm font-bold text-orange-800">Ditutup Secara Manual</p>
                        <p class="text-xs text-orange-600 mt-1">Kasus khusus telah diselesaikan oleh pihak berwenang.</p>
                    @elseif($sik->isRevoked())
                        <div class="p-6 bg-red-50 rounded-2xl border border-red-200 text-red-700 mb-2">
                            <span class="material-symbols-outlined text-5xl">block</span>
                        </div>
                        <p class="text-sm font-bold text-red-800">Izin Dicabut (Revoked)</p>
                        <p class="text-xs text-red-600 mt-1">QR Code tidak lagi berlaku.</p>
                    @elseif($sik->isRejected())
                        <div class="p-6 bg-red-50 rounded-2xl border border-red-200 text-red-700 mb-2">
                            <span class="material-symbols-outlined text-5xl">cancel</span>
                        </div>
                        <p class="text-sm font-bold text-red-800">Pengajuan Ditolak</p>
                    @else
                        <div class="p-6 bg-slate-100 rounded-2xl text-slate-400 mb-2">
                            <span class="material-symbols-outlined text-5xl">hourglass_top</span>
                        </div>
                        <p class="text-sm font-bold text-slate-700">Menunggu Persetujuan</p>
                        <p class="text-xs text-slate-500 mt-1">QR Code akan aktif secara otomatis setelah SIK disetujui oleh HOD / Atasan.</p>
                    @endif
                </div>
            </div>

            <!-- Approval & Security Gate Timeline -->
            <div class="pt-6 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Approval Information -->
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-[#2563EB]">verified</span>
                        Informasi Persetujuan (HOD / Atasan)
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Approver Ditunjuk:</span>
                            <div class="text-right">
                                <span class="font-bold text-slate-800">{{ $sik->approver ? $sik->approver->name : '-' }}</span>
                                @if($sik->is_replacement_approver)
                                    <span class="block text-[10px] text-purple-700 font-semibold">(Approver Pengganti)</span>
                                @else
                                    <span class="block text-[10px] text-slate-400">(Default Approver)</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Waktu Persetujuan:</span>
                            <span class="font-semibold text-slate-800">{{ $sik->approved_at ? $sik->approved_at->format('d M Y H:i:s') : '-' }}</span>
                        </div>
                        @if($sik->approval_note)
                            <div class="py-1">
                                <span class="text-slate-500 block mb-0.5">Catatan Approval:</span>
                                <p class="text-slate-800 bg-white p-2 rounded border border-slate-200 font-medium">{{ $sik->approval_note }}</p>
                            </div>
                        @endif
                        @if($sik->rejection_reason)
                            <div class="py-1 text-red-700">
                                <span class="font-bold block mb-0.5">Alasan Penolakan:</span>
                                <p class="bg-red-50 p-2 rounded border border-red-200">{{ $sik->rejection_reason }}</p>
                            </div>
                        @endif
                        @if($sik->revoke_reason)
                            <div class="py-1 text-red-700">
                                <span class="font-bold block mb-0.5">Alasan Pencabutan (Revoked by {{ $sik->revokedBy->name ?? '-' }}):</span>
                                <p class="bg-red-50 p-2 rounded border border-red-200">{{ $sik->revoke_reason }}</p>
                            </div>
                        @endif
                        @if($sik->close_reason)
                            <div class="py-1 text-orange-800">
                                <span class="font-bold block mb-0.5">Alasan Tutup Manual (by {{ $sik->closedBy->name ?? '-' }}):</span>
                                <p class="bg-orange-50 p-2 rounded border border-orange-200">{{ $sik->close_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Gate Scan Execution Info -->
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-[#2563EB]">gate</span>
                        Pencatatan Gerbang Security
                    </h4>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Waktu Keluar Aktual:</span>
                            <span class="font-bold {{ $sik->actual_departure_at ? 'text-purple-700' : 'text-slate-400' }}">
                                {{ $sik->actual_departure_at ? $sik->actual_departure_at->format('d M Y H:i:s') : 'Belum Keluar' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Security Keluar:</span>
                            <span class="font-semibold text-slate-800">{{ $sik->exitScannedBy ? $sik->exitScannedBy->name : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Waktu Kembali Aktual:</span>
                            <span class="font-bold {{ $sik->actual_return_at ? 'text-green-700' : 'text-slate-400' }}">
                                {{ $sik->actual_return_at ? $sik->actual_return_at->format('d M Y H:i:s') : 'Belum Kembali' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Security Kembali:</span>
                            <span class="font-semibold text-slate-800">{{ $sik->returnScannedBy ? $sik->returnScannedBy->name : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-200/60">
                            <span class="text-slate-500">Durasi Faktual di Luar:</span>
                            <span class="font-mono font-bold text-slate-800">{{ $sik->actual_duration_formatted }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-500">Keterlambatan Gerbang:</span>
                            @if($sik->is_late)
                                <span class="font-mono font-bold text-rose-600">+{{ $sik->late_duration_formatted }}</span>
                            @else
                                <span class="text-slate-500 font-mono">Tepat Waktu</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail Logs -->
            @if($sik->approvalHistories->count() > 0 || $sik->scanLogs->count() > 0)
                <div class="pt-6 border-t border-slate-200">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Riwayat Aktivitas & Audit Trail</h3>
                    <div class="space-y-3">
                        @foreach($sik->approvalHistories as $hist)
                            <div class="flex items-start gap-3 text-xs bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <span class="material-symbols-outlined text-blue-600 text-[18px] mt-0.5">history</span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800">
                                            {{ $hist->status }} oleh {{ $hist->approver ? $hist->approver->name : 'Sistem' }}
                                            <span class="text-[10px] font-semibold {{ $hist->approver_type === 'replacement' ? 'text-purple-700' : 'text-slate-500' }}">
                                                ({{ $hist->approver_type === 'replacement' ? 'Approver Pengganti' : 'Default Approver' }})
                                            </span>
                                        </span>
                                        <span class="text-slate-400 text-[11px]">{{ $hist->actioned_at->format('d M Y H:i:s') }}</span>
                                    </div>
                                    @if($hist->note)
                                        <p class="text-slate-600 mt-1">{{ $hist->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @foreach($sik->scanLogs as $scan)
                            <div class="flex items-start gap-3 text-xs bg-slate-50 p-3 rounded-lg border border-slate-200">
                                <span class="material-symbols-outlined {{ $scan->scan_type === 'EXIT' ? 'text-purple-600' : 'text-green-600' }} text-[18px] mt-0.5">qr_code_scanner</span>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800">SCAN {{ $scan->scan_type }} ({{ $scan->gate ?: 'Pos Gerbang' }}) oleh Security {{ $scan->security ? $scan->security->name : '-' }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $scan->scanned_at->format('d M Y H:i:s') }}</span>
                                    </div>
                                    @if($scan->notes)
                                        <p class="text-slate-600 mt-1">{{ $scan->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals for Manager Actions -->
@can('approve', $sik)
<div id="approveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-green-600 font-bold text-lg">
            <span class="material-symbols-outlined">check_circle</span>
            Setujui Pengajuan SIK
        </div>
        <p class="text-xs text-slate-600">Apakah Anda yakin menyetujui pengajuan SIK <strong>#{{ $sik->sik_number }}</strong> dari {{ $sik->user->name }}? QR Code izin keluar akan langsung diterbitkan.</p>
        <form action="{{ route('sik.approvals.approve', $sik) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Persetujuan (Opsional)</label>
                <textarea name="approval_note" rows="2" placeholder="Tuliskan catatan tambahan..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-green-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-xs font-semibold text-white hover:bg-green-700">Setujui SIK</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('reject', $sik)
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
                <textarea name="rejection_reason" required rows="3" placeholder="Tuliskan alasan penolakan secara jelas..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-xs font-semibold text-white hover:bg-red-700">Tolak SIK</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('revoke', $sik)
<div id="revokeModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-red-600 font-bold text-lg">
            <span class="material-symbols-outlined">block</span>
            Pencabutan Izin SIK (Revoke)
        </div>
        <p class="text-xs text-slate-600">Pencabutan izin akan membatalkan status disetujui dan menonaktifkan QR Code secara permanen.</p>
        <form action="{{ route('sik.approvals.revoke', $sik) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pencabutan Izin <span class="text-red-500">*</span></label>
                <textarea name="revoke_reason" required rows="3" placeholder="Tuliskan alasan pencabutan izin..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('revokeModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-xs font-semibold text-white hover:bg-red-700">Cabut Izin (Revoke)</button>
            </div>
        </form>
    </div>
</div>
@endcan

@can('closeManually', $sik)
<div id="closeManualModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center gap-2 text-orange-600 font-bold text-lg">
            <span class="material-symbols-outlined">lock_reset</span>
            Tutup Manual Kasus Darurat (Closed Manually)
        </div>
        <p class="text-xs text-slate-600">Digunakan ketika pemohon mengalami keadaan darurat/medis/tidak dapat kembali ke pabrik. Tindakan ini <strong>TIDAK</strong> mengisi waktu kepulangan aktual.</p>
        <form action="{{ route('sik.approvals.close-manually', $sik) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penutupan Manual <span class="text-red-500">*</span></label>
                <textarea name="close_reason" required rows="3" placeholder="mis. Pemohon mengalami insiden/darurat medis sehingga langsung dirujuk pulang dan tidak kembali ke pabrik..." class="w-full rounded-lg border border-slate-300 p-2 text-xs focus:ring-1 focus:ring-orange-500"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" onclick="closeModal('closeManualModal')" class="px-4 py-2 rounded-lg border text-xs font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-orange-600 text-xs font-semibold text-white hover:bg-orange-700">Tutup Manual Kasus</button>
            </div>
        </form>
    </div>
</div>
@endcan

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }
</script>
@endsection
