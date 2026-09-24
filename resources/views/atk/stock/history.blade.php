@extends('layouts.app')

@section('title', 'Riwayat Mutasi Stok ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.stock.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Riwayat Mutasi Stok ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Audit log lengkap setiap penambahan, pengurangan, dan penyesuaian stok.
            </p>
        </div>
    </header>

    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Tanggal & Waktu</th>
                        <th class="py-3.5 px-6">Varian (SKU)</th>
                        <th class="py-3.5 px-6">Tipe Mutasi</th>
                        <th class="py-3.5 px-6">Stok Sebelumnya</th>
                        <th class="py-3.5 px-6">Jumlah</th>
                        <th class="py-3.5 px-6">Stok Baru</th>
                        <th class="py-3.5 px-6">Pelaku</th>
                        <th class="py-3.5 px-6">Alasan / Referensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-semibold text-[#0F172A]">{{ $mov->created_at->format('d M Y H:i') }}</p>
                                <p class="text-[10px] text-slate-400 font-medium">{{ $mov->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="py-4 px-6">
                                <p class="font-semibold text-[#0F172A]">{{ $mov->variant->item->name }}</p>
                                <p class="text-xs text-slate-500 font-mono">{{ $mov->variant->name }} ({{ $mov->variant->sku }})</p>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $types = [
                                        'restock' => ['class' => 'bg-green-50 text-green-700 ring-green-600/20', 'label' => 'Restock'],
                                        'adjustment' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Penyesuaian (Opname)'],
                                        'request' => ['class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'label' => 'Pengeluaran Permintaan'],
                                    ];
                                    $t = $types[$mov->type] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($mov->type)];
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $t['class'] }} ring-1 ring-inset">
                                    {{ $t['label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500 font-medium">{{ $mov->previous_stock }} {{ $mov->variant->unit }}</td>
                            <td class="py-4 px-6">
                                @if($mov->quantity > 0)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-0.5 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        +{{ $mov->quantity }} {{ $mov->variant->unit }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20">
                                        {{ $mov->quantity }} {{ $mov->variant->unit }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">{{ $mov->new_stock }} {{ $mov->variant->unit }}</td>
                            <td class="py-4 px-6 font-medium text-slate-700">{{ $mov->actor->name }}</td>
                            <td class="py-4 px-6">
                                @if($mov->type === 'request' && $mov->reference)
                                    @php
                                        // reference is AtkRequestItem, which belongsTo AtkRequest
                                        $atkRequest = $mov->reference->request;
                                    @endphp
                                    @if($atkRequest)
                                        <a href="{{ route('atk.requests.show', $atkRequest->id) }}" class="text-xs font-bold text-[#2563EB] hover:underline font-mono">
                                            {{ $atkRequest->request_number }}
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono">Request Item #{{ $mov->reference_id }}</span>
                                    @endif
                                @else
                                    <p class="text-xs text-slate-600 italic">"{{ $mov->reason ?: '-' }}"</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center italic text-slate-400">Belum ada riwayat mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
