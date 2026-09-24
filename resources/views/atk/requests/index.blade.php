@extends('layouts.app')

@section('title', 'Daftar Request ATK - SBA')

@section('content')
<div class="space-y-6">
    <header class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Tinjau Permintaan ATK
            </h1>
            <p class="text-sm text-[#475569] mt-1">
                Proses pengajuan, persetujuan, dan pengeluaran barang ATK karyawan.
            </p>
        </div>
        <a href="{{ route('atk.history.index') }}" class="bg-white border border-[#E5E7EB] hover:bg-slate-50 text-[#0F172A] rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[18px]">history</span> Riwayat Permintaan
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

    <div class="border-b border-[#E5E7EB] flex items-center justify-between">
        <nav class="-mb-px flex gap-8" aria-label="Tabs">
            <a href="{{ route('atk.requests.index') }}" 
                class="border-b-2 py-4 px-1 text-sm font-semibold {{ is_null($status) ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} transition-all">
                Semua Aktif
            </a>
            <a href="{{ route('atk.requests.index', ['status' => 'diajukan']) }}" 
                class="border-b-2 py-4 px-1 text-sm font-semibold {{ $status === 'diajukan' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} transition-all">
                Diajukan
            </a>
            <a href="{{ route('atk.requests.index', ['status' => 'disetujui']) }}" 
                class="border-b-2 py-4 px-1 text-sm font-semibold {{ $status === 'disetujui' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} transition-all">
                Disetujui
            </a>
            <a href="{{ route('atk.requests.index', ['status' => 'diproses']) }}" 
                class="border-b-2 py-4 px-1 text-sm font-semibold {{ $status === 'diproses' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} transition-all">
                Sedang Diproses
            </a>
        </nav>
    </div>

    <!-- Active Requests Table -->
    <div class="bg-white border border-[#E5E7EB] rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#475569]">
                <thead>
                    <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                        <th class="py-3.5 px-6">Tanggal Diajukan</th>
                        <th class="py-3.5 px-6">No. Request</th>
                        <th class="py-3.5 px-6">Pemohon</th>
                        <th class="py-3.5 px-6">Item Permintaan</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse($requests as $req)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-4 px-6">
                                <p class="font-semibold text-[#0F172A]">{{ $req->created_at->format('d M Y H:i') }}</p>
                                <p class="text-[10px] text-slate-400 font-semibold">{{ $req->created_at->diffForHumans() }}</p>
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
                                        'diajukan' => ['class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'label' => 'Diajukan'],
                                        'disetujui' => ['class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'label' => 'Disetujui'],
                                        'diproses' => ['class' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'label' => 'Diproses'],
                                    ];
                                    $st = $statuses[$req->status] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($req->status)];
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $st['class'] }} ring-1 ring-inset">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('atk.requests.show', $req->id) }}" class="text-xs font-bold text-white bg-[#2563EB] hover:bg-[#1D4ED8] px-3.5 py-2 rounded-lg transition-colors inline-flex items-center gap-1 shadow-sm">
                                    {{ auth()->user()->isGa() ? 'Tinjau Detail' : 'Lihat Detail' }} <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center italic text-slate-400">Tidak ada permintaan aktif yang memerlukan tinjauan.</td>
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
