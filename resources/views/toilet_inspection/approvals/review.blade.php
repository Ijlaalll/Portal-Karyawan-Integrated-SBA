@extends('layouts.app')

@section('title', 'Tinjau Inspeksi - SBA')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Tinjau Inspeksi
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                ID Log: #{{ $inspection->id }} &bull; Dikirim oleh {{ $inspection->inspector->name }} pada {{ $inspection->inspected_at->format('d M Y, H:i') }}
            </p>
        </div>
        <a href="{{ route('toilet_inspection.dashboard') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
            Batal
        </a>
    </header>

    @if ($errors->any())
        <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="text-sm font-semibold text-red-800">Harap perbaiki kesalahan berikut:</div>
            <ul class="mt-2 list-disc list-inside text-xs text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Location details -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
        <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Lokasi</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
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

    <!-- Review Action Panel -->
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
        <h3 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Tindakan Ulasan</h3>
        
        <form id="review-form" method="POST" class="space-y-4" onsubmit="this.querySelectorAll('button[type=submit]').forEach(btn => btn.disabled = true); return true;">
            @csrf
            
            <div class="space-y-1.5">
                <label for="notes" class="block text-sm font-medium text-[#0F172A]">Catatan / Feedback SPV</label>
                <textarea name="notes" id="notes" rows="4" placeholder="Masukkan catatan di sini. Catatan wajib diisi jika Anda memilih untuk Menolak inspeksi ini."
                    class="block w-full rounded-md border border-[#E5E7EB] px-3 py-2 text-sm text-[#0F172A] shadow-sm focus:border-[#2563EB] focus:outline-none focus:ring-1 focus:ring-[#2563EB]"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[#E5E7EB]">
                <button type="submit" onclick="submitReviewForm('{{ route('toilet_inspection.reject', $inspection) }}')" class="rounded border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100 transition-colors">
                    Tolak Inspeksi
                </button>
                <button type="submit" onclick="submitReviewForm('{{ route('toilet_inspection.approve', $inspection) }}')" class="rounded bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500">
                    Setujui Inspeksi
                </button>
            </div>
        </form>
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
    function submitReviewForm(actionUrl) {
        const form = document.getElementById('review-form');
        form.action = actionUrl;
    }
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
