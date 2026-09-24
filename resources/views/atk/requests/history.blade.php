@extends('layouts.app')

@section('title', 'Riwayat Request ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.requests.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Riwayat Permintaan ATK
            </h1>
            <p class="text-sm text-[#475569] mt-0.5">
                Audit log lengkap setiap permintaan ATK yang telah selesai, ditolak, atau dibatalkan.
            </p>
        </div>
    </header>

    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Tanggal Selesai/Batal</th>
                        <th class="py-3.5 px-6">No. Request</th>
                        <th class="py-3.5 px-6">Pemohon</th>
                        <th class="py-3.5 px-6">Item Permintaan</th>
                        <th class="py-3.5 px-6">Status Akhir</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-semibold text-[#0F172A]">{{ $req->updated_at->format('d M Y H:i') }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold">{{ $req->updated_at->diffForHumans() }}</p>
                            </td>
                            <td class="py-4 px-6 font-semibold text-[#2563EB] font-mono">{{ $req->request_number }}</td>
                            <td class="py-4 px-6 font-semibold text-[#0F172A]">{{ $req->user->name }}</td>
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
                                @php
                                    $statuses = [
                                        'selesai' => ['class' => 'bg-slate-50 text-slate-700 ring-slate-600/10', 'label' => 'Selesai'],
                                        'ditolak' => ['class' => 'bg-red-50 text-red-700 ring-red-600/20', 'label' => 'Ditolak'],
                                        'dibatalkan' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Batal'],
                                    ];
                                    $st = $statuses[$req->status] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($req->status)];
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $st['class'] }} ring-1 ring-inset">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('atk.requests.show', $req->id) }}" class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 px-3.5 py-2 rounded-lg transition-colors inline-flex items-center gap-1 shadow-sm">
                                    Detail <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center italic text-slate-400">Belum ada riwayat permintaan yang ditutup.</td>
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
@endsection
