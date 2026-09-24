<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak SIK - {{ $sik->sik_number }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .page-container { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: 100% !important; }
        }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 p-6 flex flex-col items-center">

    <!-- Top Toolbar (Hidden when printing) -->
    <div class="max-w-3xl w-full mb-4 flex justify-between items-center no-print">
        <a href="{{ route('sik.show', $sik) }}" class="text-sm font-semibold text-blue-600 hover:underline">
            &larr; Kembali ke Detail SIK
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg shadow-sm">
            Cetak Surat (Print)
        </button>
    </div>

    <!-- Official SIK Sheet -->
    <div class="page-container max-w-3xl w-full bg-white border border-slate-300 shadow-lg rounded-xl p-8 text-slate-900">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b-2 border-slate-900">
            <div>
                <h2 class="text-lg font-black tracking-wider text-slate-900">PT SOLUSI BANGUN ANDALAS</h2>
                <p class="text-[11px] text-slate-600">Lhoknga Plant - Aceh Besar, Nanggroe Aceh Darussalam</p>
                <h1 class="text-xl font-extrabold uppercase mt-2 text-slate-900">SURAT IZIN KELUAR (SIK)</h1>
            </div>
            <div class="text-right">
                <div class="text-xs font-mono font-bold bg-slate-100 border border-slate-300 px-3 py-1.5 rounded">
                    NO: {{ $sik->sik_number }}
                </div>
                <div class="text-[10px] text-slate-500 mt-1">Status: <span class="font-bold text-slate-800">{{ strtoupper($sik->status) }}</span></div>
            </div>
        </div>

        <!-- Content Details -->
        <div class="py-6 space-y-6 text-sm">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">I. Data Pemohon</h3>
                <table class="w-full text-xs">
                    <tbody>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 w-44">Nama Karyawan</td>
                            <td class="py-1.5 font-bold">: {{ $sik->user->name }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Nomor Karyawan</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->employee_id ?: ($sik->user->employee_id ?: '-') }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Departemen</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->department }}</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-500">Jabatan / Posisi</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->position ?: '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">II. Rincian Izin Keluar</h3>
                <table class="w-full text-xs">
                    <tbody>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500 w-44">Jenis Keperluan</td>
                            <td class="py-1.5 font-bold">: {{ strtoupper($sik->purpose_type) }} ({{ $sik->purpose_type === 'dinas' ? 'Tugas Kedinasan Perusahaan' : 'Keperluan Pribadi / Keluarga' }})</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Tujuan / Keperluan</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->purpose }}</td>
                        </tr>
                        @if($sik->purpose_detail)
                            <tr class="border-b border-slate-100">
                                <td class="py-1.5 text-slate-500">Keterangan Tambahan</td>
                                <td class="py-1.5">: {{ $sik->purpose_detail }}</td>
                            </tr>
                        @endif
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Tanggal Izin</td>
                            <td class="py-1.5 font-bold">: {{ $sik->permit_date->format('d F Y') }}</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Rencana Waktu Keluar</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->expected_departure_time }} WIB</td>
                        </tr>
                        <tr class="border-b border-slate-100">
                            <td class="py-1.5 text-slate-500">Rencana Waktu Kembali</td>
                            <td class="py-1.5 font-semibold">: {{ $sik->expected_return_time }} WIB</td>
                        </tr>
                        <tr>
                            <td class="py-1.5 text-slate-500">Kendaraan Digunakan</td>
                            <td class="py-1.5 font-semibold">: 
                                @if($sik->vehicle_type === 'company_vehicle')
                                    Kendaraan Dinas SBA ({{ $sik->vehicle_details ?: '-' }})
                                @elseif($sik->vehicle_type === 'private_vehicle')
                                    Kendaraan Pribadi ({{ $sik->vehicle_details ?: '-' }})
                                @else
                                    Tanpa Kendaraan / Jalan Kaki
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- QR & Gate Verification Record -->
            <div class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Verifikasi QR Code Gate Security</h4>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">Token: {{ $sik->qr_token ?: 'BELUM_DITERBITKAN' }}</p>
                    <div class="mt-2 text-[11px] text-slate-600 space-y-0.5">
                        <div>Keluar Aktual: <span class="font-semibold text-slate-800">{{ $sik->actual_departure_at ? $sik->actual_departure_at->format('d/m/Y H:i:s') : 'Belum tercatat' }}</span></div>
                        <div>Kembali Aktual: <span class="font-semibold text-slate-800">{{ $sik->actual_return_at ? $sik->actual_return_at->format('d/m/Y H:i:s') : 'Belum tercatat' }}</span></div>
                    </div>
                </div>
                @if($sik->qr_token && ($sik->isApproved() || $sik->isOutside() || $sik->isOverdue() || $sik->isReturned()))
                    <div class="bg-white p-2 rounded-lg border border-slate-300">
                        {!! app(\App\Services\QrCodeService::class)->generateSvg($sik->qr_token, 100) !!}
                    </div>
                @endif
            </div>
        </div>

        <!-- Signatures (3 Columns) -->
        <div class="pt-8 border-t border-slate-300 grid grid-cols-3 gap-4 text-center text-xs">
            <div>
                <p class="text-slate-500">Pemohon</p>
                <div class="h-20 flex items-center justify-center font-bold text-slate-400 italic">
                    ( Ditandatangani Digital )
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1">{{ $sik->user->name }}</p>
                <p class="text-[10px] text-slate-500">{{ $sik->position ?: 'Karyawan' }}</p>
            </div>

            <div>
                <p class="text-slate-500">Atasan / HOD Berwenang</p>
                <div class="h-20 flex items-center justify-center font-bold text-green-700 text-[11px]">
                    @if($sik->approved_at)
                        DISETUJUI DIGITAL<br>{{ $sik->approved_at->format('d/m/Y H:i') }}
                    @else
                        ( Menunggu Persetujuan )
                    @endif
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1">{{ $sik->approver ? $sik->approver->name : '................................' }}</p>
                <p class="text-[10px] text-slate-500">Approver Departemen</p>
            </div>

            <div>
                <p class="text-slate-500">Petugas Pos Security</p>
                <div class="h-20 flex items-center justify-center font-bold text-purple-700 text-[11px]">
                    @if($sik->actual_departure_at)
                        TERVALIDASI GATE<br>{{ $sik->actual_departure_at->format('d/m/Y H:i') }}
                    @else
                        ( Paraf / Scan )
                    @endif
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-400 pt-1">{{ $sik->exitScannedBy ? $sik->exitScannedBy->name : '................................' }}</p>
                <p class="text-[10px] text-slate-500">Security Guard</p>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-200 text-[10px] text-slate-400 text-center">
            Dokumen elektronik ini sah dan diakui secara resmi dalam Sistem Otomasi Perizinan PT Solusi Bangun Andalas. Dicetak pada {{ date('d M Y H:i') }} WIB.
        </div>
    </div>

</body>
</html>
