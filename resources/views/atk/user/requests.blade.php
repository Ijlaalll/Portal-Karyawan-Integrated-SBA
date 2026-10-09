@extends('layouts.app')

@section('title', 'Permintaan ATK Saya - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Permintaan ATK Saya
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Pantau status pengajuan, persetujuan, dan penyerahan ATK Anda.
            </p>
        </div>
        <a href="{{ route('atk.catalog') }}" class="bg-[#2563EB] text-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">shopping_cart</span> Buka Katalog
        </a>
    </header>

    @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-md bg-red-50 p-4 border border-red-200 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Requests List Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Tanggal Pengajuan</th>
                        <th class="py-3.5 px-6">No. Request</th>
                        <th class="py-3.5 px-6">Item ATK</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($requests as $req)
                        @php
                            $statuses = [
                                'diajukan' => ['class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'label' => 'Diajukan'],
                                'disetujui' => ['class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'label' => 'Disetujui'],
                                'diproses' => ['class' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'label' => 'Diproses'],
                                'selesai' => ['class' => 'bg-slate-50 text-slate-700 ring-slate-600/10', 'label' => 'Selesai'],
                                'ditolak' => ['class' => 'bg-red-50 text-red-700 ring-red-600/20', 'label' => 'Ditolak'],
                                'dibatalkan' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Batal'],
                            ];
                            $st = $statuses[$req->status] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($req->status)];
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-semibold text-[#0F172A]">{{ $req->created_at->format('d M Y H:i') }}</p>
                                <p class="text-[10px] text-slate-400 font-medium">{{ $req->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs font-semibold text-slate-600">{{ $req->request_number }}</td>
                            <td class="py-4 px-6">
                                <div class="space-y-1">
                                    @foreach($req->items as $item)
                                        <p class="text-xs text-slate-700 font-medium">
                                            {{ $item->variant->item->name }} - {{ $item->variant->name }} 
                                            <span class="text-slate-400 font-bold">x{{ $item->quantity }}</span>
                                        </p>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $st['class'] }} ring-1 ring-inset">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right flex items-center justify-end gap-x-2">
                                <button type="button" 
                                    data-request="{{ json_encode($req) }}"
                                    onclick="openDetailModal(this)"
                                    class="text-xs font-bold text-[#2563EB] hover:text-[#1D4ED8] bg-[#EFF6FF] px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">visibility</span> Lacak & Detail
                                </button>
                                
                                @if($req->status === 'diajukan')
                                    <button type="button" 
                                        onclick="openCancelModal('{{ $req->id }}', '{{ $req->request_number }}')"
                                        class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 px-2.5 py-1.5 rounded transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">cancel</span> Batalkan
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center italic text-slate-400">Anda belum pernah mengajukan permintaan ATK.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Request Detail & Track Timeline Modal -->
<div id="detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl overflow-hidden transform transition-all duration-200 scale-95 flex flex-col max-h-[90vh]" style="width: 480px; max-width: 100%;">
        <!-- Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-[#0F172A]" id="detail-title">Detail Permintaan</h3>
                <p class="text-[10px] text-slate-400 mt-0.5" id="detail-subtitle"></p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-50 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-1 text-sm text-[#475569]">
            <!-- Items Requested -->
            <div class="space-y-2">
                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Item ATK</h4>
                <div class="space-y-3" id="detail-items-list">
                    <!-- Injected via JS -->
                </div>
            </div>

            <!-- Notes -->
            <div class="mt-6 space-y-2 hidden" id="detail-note-container">
                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Catatan</h4>
                <p class="text-xs text-slate-600 bg-slate-50/50 border border-slate-100 p-3 rounded-xl italic leading-relaxed" id="detail-note"></p>
            </div>

            <!-- Rejection Reason -->
            <div class="mt-6 space-y-2 bg-red-50/75 border border-red-100 p-4 rounded-xl hidden" id="detail-rejection-container">
                <h4 class="text-xs font-bold text-red-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-red-600">error</span> Alasan Penolakan GA
                </h4>
                <p class="text-xs text-red-800 italic mt-1 leading-relaxed" id="detail-rejection-reason"></p>
            </div>

            <!-- Cancellation Reason -->
            <div class="mt-6 space-y-2 bg-amber-50/75 border border-amber-100 p-4 rounded-xl hidden" id="detail-cancellation-container">
                <h4 class="text-xs font-bold text-amber-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-amber-600">info</span> Alasan Pembatalan
                </h4>
                <p class="text-xs text-amber-800 italic mt-1 leading-relaxed" id="detail-cancellation-reason"></p>
            </div>

            <!-- Track Timeline -->
            <div class="mt-6 space-y-4 pt-2">
                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Lacak Status</h4>
                <div class="flow-root pl-2">
                    <ul role="list" class="-mb-8" id="detail-timeline">
                        <!-- Injected via JS -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-900 transition-colors shadow-sm">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Cancel Request Form Modal -->
<div id="cancel-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="bg-white rounded-xl border border-slate-200 shadow-2xl max-w-md w-full overflow-hidden transform transition-all duration-200 scale-95">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-[#0F172A]">Batalkan Permintaan ATK</h3>
            <button type="button" onclick="closeCancelModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[24px]">close</span>
            </button>
        </div>

        <form id="cancel-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">No. Request</p>
                <p class="text-base font-bold text-[#0F172A] mt-0.5" id="cancel-modal-req-number"></p>
            </div>

            <div class="space-y-2">
                <label for="cancellation_reason" class="block text-sm font-semibold text-[#0F172A]">Alasan Pembatalan <span class="text-red-500">*</span></label>
                <textarea name="cancellation_reason" id="cancellation_reason" required rows="3" placeholder="Masukkan alasan Anda membatalkan permintaan ini..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] sm:text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeCancelModal()" class="px-4 py-2 border border-gray-300 rounded text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded text-sm font-semibold hover:bg-red-700 transition-colors shadow-sm">
                    Konfirmasi Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str || '';
        return div.innerHTML;
    }

    function openDetailModal(button) {
        const req = JSON.parse(button.getAttribute('data-request'));
        document.getElementById('detail-title').textContent = `Lacak Permintaan ${req.request_number}`;
        
        const dateStr = new Date(req.created_at).toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('detail-subtitle').textContent = `Diajukan pada ${dateStr}`;

        // Populate Items List
        const itemsList = document.getElementById('detail-items-list');
        let itemsHtml = '';
        req.items.forEach(item => {
            const itemName = escapeHtml(item.variant?.item?.name || '');
            const variantName = escapeHtml(item.variant?.name || '');
            const sku = escapeHtml(item.variant?.sku || '');
            const unit = escapeHtml(item.variant?.unit || '');
            const qty = escapeHtml(String(item.quantity ?? 0));

            itemsHtml += `
                <div class="p-4 border border-slate-100 rounded-xl flex items-center justify-between bg-white shadow-sm hover:shadow transition-shadow">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-[#0F172A] text-sm truncate" title="${itemName}">${itemName}</p>
                        <p class="text-[11px] text-slate-500 font-semibold mt-0.5">${variantName} <span class="font-mono text-slate-300">|</span> ${sku}</p>
                    </div>
                    <span class="font-bold text-[#2563EB] bg-blue-50 border border-blue-100/50 px-3 py-1.5 rounded-xl text-xs shrink-0 ml-4">x${qty} ${unit}</span>
                </div>
            `;
        });
        itemsList.innerHTML = itemsHtml;

        // Populate Note
        const noteContainer = document.getElementById('detail-note-container');
        if (req.note) {
            document.getElementById('detail-note').textContent = `"${req.note}"`;
            noteContainer.classList.remove('hidden');
        } else {
            noteContainer.classList.add('hidden');
        }

        // Populate Rejection
        const rejectContainer = document.getElementById('detail-rejection-container');
        if (req.status === 'ditolak' && req.rejection_reason) {
            document.getElementById('detail-rejection-reason').textContent = `"${req.rejection_reason}"`;
            rejectContainer.classList.remove('hidden');
        } else {
            rejectContainer.classList.add('hidden');
        }

        // Populate Cancellation
        const cancelContainer = document.getElementById('detail-cancellation-container');
        if (req.status === 'dibatalkan' && req.cancellation_reason) {
            document.getElementById('detail-cancellation-reason').textContent = `"${req.cancellation_reason}"`;
            cancelContainer.classList.remove('hidden');
        } else {
            cancelContainer.classList.add('hidden');
        }

        // Populate Timeline History
        const timeline = document.getElementById('detail-timeline');
        let timelineHtml = '';
        const totalHistories = req.histories.length;
        
        req.histories.forEach((hist, index) => {
            const isLast = index === totalHistories - 1;
            
            const histDate = new Date(hist.created_at).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit'
            });

            const statusColors = {
                'diajukan': 'bg-blue-600 text-white',
                'disetujui': 'bg-emerald-600 text-white',
                'ditolak': 'bg-rose-600 text-white',
                'diproses': 'bg-indigo-600 text-white',
                'selesai': 'bg-teal-600 text-white',
                'dibatalkan': 'bg-slate-500 text-white',
            };
            const icons = {
                'diajukan': 'add_comment',
                'disetujui': 'check_circle',
                'ditolak': 'cancel',
                'diproses': 'local_shipping',
                'selesai': 'check',
                'dibatalkan': 'cancel',
            };

            const colorClass = statusColors[hist.status] || 'bg-slate-400 text-white';
            const iconName = icons[hist.status] || 'info';
            const statusUpper = escapeHtml((hist.status || '').toUpperCase());
            const actorName = escapeHtml(hist.actor?.name || 'Sistem');
            const notes = escapeHtml(hist.notes || '');

            timelineHtml += `
                <li>
                    <div class="relative pb-8">
                        ${!isLast ? '<span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>' : ''}
                        <div class="relative flex items-start gap-3">
                            <div class="shrink-0">
                                <span class="h-8 w-8 rounded-full flex items-center justify-center ${colorClass} ring-4 ring-white shadow-sm">
                                    <span class="material-symbols-outlined text-[15px]">${iconName}</span>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <p class="text-xs font-bold text-slate-800">${statusUpper}</p>
                                <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Oleh ${actorName} <span class="font-mono text-slate-300">|</span> ${histDate}</p>
                                <p class="text-[10px] text-slate-600 italic mt-1.5 bg-slate-50/50 border border-slate-100 p-2.5 rounded-lg leading-relaxed">"${notes}"</p>
                            </div>
                        </div>
                    </div>
                </li>
            `;
        });
        timeline.innerHTML = timelineHtml;

        document.getElementById('detail-modal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').classList.add('hidden');
    }

    function openCancelModal(requestId, requestNumber) {
        document.getElementById('cancel-form').action = `/atk/requests/${requestId}/cancel`;
        document.getElementById('cancel-modal-req-number').textContent = requestNumber;
        document.getElementById('cancellation_reason').value = '';
        
        document.getElementById('cancel-modal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancel-modal').classList.add('hidden');
    }
</script>
@endsection
