@extends('layouts.app')

@section('title', 'Detail Inspeksi - SBA')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Detail Inspeksi
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                ID Log: #{{ $inspection->id }} &bull; Diinspeksi oleh {{ $inspection->inspector->name }} pada {{ $inspection->inspected_at->format('d M Y, H:i') }}
            </p>
        </div>
        <a href="{{ route('toilet_inspection.dashboard') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
            Kembali ke Dashboard
        </a>
    </header>

    <!-- Overview Details & Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Location details -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Lokasi</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Gedung</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->building }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Lantai</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->floor }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Area / Kantor</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->area }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Toilet</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->toilet_name }}</span>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Status</h2>
                <div class="mt-4">
                    @php
                        $badges = [
                            'submitted' => 'bg-[#FFFBEB] text-[#D97706] ring-[#D97706]/20',
                            'approved' => 'bg-[#F0FDF4] text-[#16A34A] ring-[#16A34A]/20',
                            'rejected' => 'bg-[#FEF2F2] text-[#DC2626] ring-[#DC2626]/20',
                            'draft' => 'bg-[#F8FAFC] text-[#64748B] ring-[#E2E8F0]',
                        ];
                        $badgeClass = $badges[$inspection->status] ?? $badges['draft'];
                    @endphp
                    <span class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-semibold {{ $badgeClass }} ring-1 ring-inset">
                        {{ $inspection->status === 'submitted' ? 'Menunggu Ulasan' : ($inspection->status === 'approved' ? 'Disetujui' : ($inspection->status === 'rejected' ? 'Ditolak' : 'Draft')) }}
                    </span>

                    @if(auth()->user()->isSpv() && $inspection->status === 'submitted')
                        <div class="mt-4">
                            <a href="{{ route('toilet_inspection.review', $inspection) }}" class="inline-flex items-center justify-center w-full rounded bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                                Ulas Inspeksi
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="text-xs text-[#94A3B8] mt-4">
                Terakhir diperbarui pada: {{ $inspection->updated_at->format('d M Y, H:i') }}
            </div>
        </div>
    </div>

    <!-- SPV Review Log Details (if approved or rejected) -->
    @if ($inspection->approval)
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Log Tinjauan SPV</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-semibold text-[#64748B] uppercase">SPV / Peninjau</span>
                        <span class="text-[#0F172A] font-medium">{{ $inspection->approval->approver->name }} ({{ $inspection->approval->approver->email }})</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-[#64748B] uppercase">Tanggal Keputusan</span>
                        <span class="text-[#0F172A] font-medium">{{ $inspection->approval->actioned_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Catatan Tinjauan SPV</span>
                    <p class="text-[#0F172A] mt-1 bg-[#F8FAFC] p-3 rounded border border-[#E5E7EB] text-xs whitespace-pre-wrap">
                        {{ $inspection->approval->notes ?: 'Tidak ada catatan tambahan yang diberikan oleh peninjau.' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Checklist Results and Evidence Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Checklist Table -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg shadow-sm col-span-3 overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5E7EB]">
                <h3 class="text-base font-bold text-[#0F172A]">Detail Checklist</h3>
            </div>
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Hasil</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-[#E5E7EB] text-sm text-[#0F172A]">
                    @foreach ($inspection->results as $res)
                        <tr>
                            <td class="px-6 py-4 font-medium">{{ $res->checklistItem->name }}</td>
                            <td class="px-6 py-4">
                                @if ($res->is_ok)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 ring-1 ring-inset ring-green-600/20">✓ OK</span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">✗ Tidak OK</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[#475569]">{{ $res->remarks ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Evidence Photo & Inspector Notes -->
        <div class="col-span-2 space-y-6">
            <!-- Photo Evidence Card -->
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-[#0F172A]">Foto Bukti</h3>
                @if ($inspection->photo_path)
                    <div class="cursor-pointer rounded-lg overflow-hidden border border-[#E5E7EB] bg-[#F8FAFC] aspect-video flex items-center justify-center" onclick="openLightbox('{{ asset('storage/' . $inspection->photo_path) }}')">
                        <img src="{{ asset('storage/' . $inspection->photo_path) }}" alt="Inspection Evidence Photo" class="object-cover max-h-64 max-w-full hover:scale-105 transition-transform duration-200">
                    </div>
                    <p class="text-center text-xs text-[#64748B] mt-1">Klik gambar untuk memperbesar</p>
                @else
                    <div class="rounded-lg border-2 border-dashed border-[#E5E7EB] p-8 text-center text-xs text-[#94A3B8]">
                        Tidak ada foto bukti yang diunggah.
                    </div>
                @endif
            </div>

            <!-- General Notes Card -->
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-3">
                <h3 class="text-base font-bold text-[#0F172A]">Catatan Petugas</h3>
                <p class="text-sm text-[#475569] bg-[#F8FAFC] p-4 rounded border border-[#E5E7EB] italic">
                    {{ $inspection->notes ?: 'Tidak ada catatan umum yang direkam.' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 cursor-pointer" onclick="closeLightbox()">
    <div class="max-w-4xl max-h-full relative flex flex-col justify-center items-center">
        <img id="lightbox-img" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" src="" alt="Enlarged Evidence">
        <p class="text-white text-xs mt-3 bg-black/40 px-3 py-1.5 rounded-full">Klik di mana saja untuk menutup</p>
    </div>
</div>

<script>
    function openLightbox(src) {
        document.getElementById('lightbox-img').src = src;
        document.getElementById('lightbox').classList.remove('hidden');
    }
    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.getElementById('lightbox-img').src = '';
    }
</script>
@endsection
