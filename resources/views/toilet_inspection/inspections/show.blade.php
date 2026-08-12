@extends('layouts.app')

@section('title', 'Inspection Details - SBA')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Inspection Details
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Log ID: #{{ $inspection->id }} &bull; Inspected by {{ $inspection->inspector->name }} on {{ $inspection->inspected_at->format('d M Y, H:i') }}
            </p>
        </div>
        <a href="{{ route('toilet_inspection.dashboard') }}" class="rounded border border-[#E5E7EB] bg-white px-4 py-2.5 text-sm font-semibold text-[#0F172A] shadow-sm hover:bg-[#F9FAFB] transition-colors">
            Back to Dashboard
        </a>
    </header>

    <!-- Overview Details & Status -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Location details -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm col-span-2 space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Location</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Building</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->building }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Floor</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->floor }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Area / Office</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->area }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Toilet / Gender</span>
                    <span class="text-[#0F172A] font-medium">{{ $inspection->location->toilet_name }} ({{ ucfirst($inspection->location->gender) }})</span>
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
                        {{ $inspection->status === 'submitted' ? 'Pending Review' : ucfirst($inspection->status) }}
                    </span>

                    @if(auth()->user()->isSpv() && $inspection->status === 'submitted')
                        <div class="mt-4">
                            <a href="{{ route('toilet_inspection.review', $inspection) }}" class="inline-flex items-center justify-center w-full rounded bg-[#2563EB] px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors">
                                Review Inspection
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="text-xs text-[#94A3B8] mt-4">
                Last updated at: {{ $inspection->updated_at->format('d M Y, H:i') }}
            </div>
        </div>
    </div>

    <!-- SPV Review Log Details (if approved or rejected) -->
    @if ($inspection->approval)
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Supervisor Review Log</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="space-y-4">
                    <div>
                        <span class="block text-xs font-semibold text-[#64748B] uppercase">Supervisor / Approver</span>
                        <span class="text-[#0F172A] font-medium">{{ $inspection->approval->approver->name }} ({{ $inspection->approval->approver->email }})</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-[#64748B] uppercase">Decision Date</span>
                        <span class="text-[#0F172A] font-medium">{{ $inspection->approval->actioned_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-[#64748B] uppercase">Supervisor Review Notes</span>
                    <p class="text-[#0F172A] mt-1 bg-[#F8FAFC] p-3 rounded border border-[#E5E7EB] text-xs whitespace-pre-wrap">
                        {{ $inspection->approval->notes ?: 'No additional notes provided by reviewer.' }}
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
                <h3 class="text-base font-bold text-[#0F172A]">Checklist Details</h3>
            </div>
            <table class="min-w-full divide-y divide-[#E5E7EB]">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Item</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Result</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase">Remarks</th>
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
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20">✗ Not OK</span>
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
                <h3 class="text-base font-bold text-[#0F172A]">Photo Evidence</h3>
                @if ($inspection->photo_path)
                    <div class="cursor-pointer rounded-lg overflow-hidden border border-[#E5E7EB] bg-[#F8FAFC] aspect-video flex items-center justify-center" onclick="openLightbox('{{ asset('storage/' . $inspection->photo_path) }}')">
                        <img src="{{ asset('storage/' . $inspection->photo_path) }}" alt="Inspection Evidence Photo" class="object-cover max-h-64 max-w-full hover:scale-105 transition-transform duration-200">
                    </div>
                    <p class="text-center text-xs text-[#64748B] mt-1">Click image to view larger</p>
                @else
                    <div class="rounded-lg border-2 border-dashed border-[#E5E7EB] p-8 text-center text-xs text-[#94A3B8]">
                        No evidence photo uploaded.
                    </div>
                @endif
            </div>

            <!-- General Notes Card -->
            <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 shadow-sm space-y-3">
                <h3 class="text-base font-bold text-[#0F172A]">Inspector's Notes</h3>
                <p class="text-sm text-[#475569] bg-[#F8FAFC] p-4 rounded border border-[#E5E7EB] italic">
                    {{ $inspection->notes ?: 'No general notes recorded.' }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-50 hidden bg-black/80 flex items-center justify-center p-4 cursor-pointer" onclick="closeLightbox()">
    <div class="max-w-4xl max-h-full relative flex flex-col justify-center items-center">
        <img id="lightbox-img" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain" src="" alt="Enlarged Evidence">
        <p class="text-white text-xs mt-3 bg-black/40 px-3 py-1.5 rounded-full">Click anywhere to close</p>
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
