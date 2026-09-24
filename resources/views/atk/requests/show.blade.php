@extends('layouts.app')

@section('title', 'Tinjau Request ' . $request->request_number . ' - SBA')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <header class="flex items-center gap-4">
        <a href="{{ route('atk.requests.index') }}" class="text-slate-600 hover:text-slate-900 transition-colors">
            <span class="material-symbols-outlined text-[28px] align-middle">arrow_back</span>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold leading-tight tracking-tight text-[#0F172A]">
                    Detail Permintaan {{ $request->request_number }}
                </h1>
                @php
                    $statuses = [
                        'diajukan' => ['class' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20', 'label' => 'Diajukan'],
                        'disetujui' => ['class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20', 'label' => 'Disetujui'],
                        'diproses' => ['class' => 'bg-blue-50 text-blue-700 ring-blue-600/20', 'label' => 'Diproses'],
                        'selesai' => ['class' => 'bg-slate-50 text-slate-700 ring-slate-600/10', 'label' => 'Selesai'],
                        'ditolak' => ['class' => 'bg-red-50 text-red-700 ring-red-600/20', 'label' => 'Ditolak'],
                        'dibatalkan' => ['class' => 'bg-amber-50 text-amber-700 ring-amber-600/20', 'label' => 'Batal'],
                    ];
                    $st = $statuses[$request->status] ?? ['class' => 'bg-slate-50 text-slate-600', 'label' => ucfirst($request->status)];
                @endphp
                <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold {{ $st['class'] }} ring-1 ring-inset">
                    {{ $st['label'] }}
                </span>
            </div>
            <p class="text-sm text-[#475569] mt-0.5">
                Diajukan pada {{ $request->created_at->format('d M Y H:i') }} ({{ $request->created_at->diffForHumans() }})
            </p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details (Items list, Notes) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Requester details card -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Informasi Pemohon</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase">Nama</span>
                        <p class="font-bold text-[#0F172A] mt-0.5">{{ $request->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase">Email / Peran</span>
                        <p class="text-slate-600 mt-0.5">{{ $request->user->email }} ({{ ucfirst($request->user->role) }})</p>
                    </div>
                    @if($request->note)
                        <div class="col-span-2 bg-slate-50 p-3 rounded-lg border border-slate-200">
                            <span class="text-xs font-semibold text-slate-500 uppercase">Catatan Karyawan</span>
                            <p class="text-slate-700 mt-1 italic text-xs">"{{ $request->note }}"</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Items list card -->
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2">Daftar Barang Diminta</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#475569]">
                        <thead>
                            <tr class="border-b border-[#E5E7EB] text-xs font-bold text-[#0F172A] uppercase bg-[#F9FAFB]">
                                <th class="py-2.5 px-4">Barang & Varian</th>
                                <th class="py-2.5 px-4 text-center">Jumlah Diminta</th>
                                <th class="py-2.5 px-4">Harga Beli</th>
                                <th class="py-2.5 px-4">Subtotal</th>
                                <th class="py-2.5 px-4">Stok Sistem</th>
                                <th class="py-2.5 px-4">Ketersediaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @foreach($request->items as $item)
                                @php
                                    $variant = $item->variant;
                                    $availability = $variant->availability_status;
                                    $tags = [
                                        'Tersedia' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'Stok Menipis' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                        'Habis' => 'bg-red-50 text-red-700 ring-red-600/20'
                                    ];
                                    $tagClass = $tags[$availability] ?? 'bg-slate-50 text-slate-600';
                                @endphp
                                <tr>
                                    <td class="py-3 px-4">
                                        <p class="font-semibold text-[#0F172A]">{{ $variant->item->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono">{{ $variant->name }} ({{ $variant->sku }})</p>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-[#0F172A] bg-indigo-50/30">
                                        {{ $item->quantity }} {{ $variant->unit }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">
                                        Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700 bg-indigo-50/10">
                                        Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">
                                        {{ $variant->stock }} {{ $variant->unit }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $tagClass }} ring-1 ring-inset">
                                            {{ $availability }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-200 mt-4 text-sm font-bold text-[#0F172A]">
                    <span>Total Nilai Barang (Snapshot)</span>
                    <span class="text-base text-[#2563EB]">Rp{{ number_format($request->items->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Workflow Action Box -->
            @if(in_array($request->status, ['diajukan', 'disetujui', 'diproses']) && auth()->user()->isGa())
                <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                    <h3 class="text-sm font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2 flex items-center gap-1.5 text-[#2563EB]">
                        <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span> Alur Tindakan GA
                    </h3>

                    @if($request->status === 'diajukan')
                        <div class="flex flex-wrap gap-3">
                            <form action="{{ route('atk.requests.approve', $request->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menyetujui permintaan ini? Karyawan akan dinotifikasi.');"
                                    class="bg-emerald-600 text-white rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-emerald-700 transition-colors shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">done</span> Setujui Permintaan
                                </button>
                            </form>
                            <button type="button" onclick="toggleRejectionBox()"
                                class="bg-red-50 text-red-600 border border-red-200 rounded px-4 py-2 font-semibold text-sm flex items-center gap-1.5 hover:bg-red-100 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">close</span> Tolak Permintaan
                            </button>
                        </div>

                        <!-- Reject Form Container -->
                        <div id="rejection-box" class="hidden mt-4 bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-3">
                            <form action="{{ route('atk.requests.reject', $request->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="space-y-1">
                                    <label for="rejection_reason" class="block text-xs font-bold text-slate-700 uppercase">Alasan Penolakan <span class="text-red-500">*</span></label>
                                    <textarea name="rejection_reason" id="rejection_reason" required rows="3" placeholder="Contoh: Barang tidak berkaitan dengan kebutuhan dinas atau stok habis total."
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#2563EB] focus:ring-[#2563EB] text-xs"></textarea>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="toggleRejectionBox()" class="px-3 py-1.5 border border-gray-300 rounded text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded text-xs font-semibold hover:bg-red-700 transition-colors">
                                        Kirim Penolakan
                                    </button>
                                </div>
                            </form>
                        </div>
                    @elseif($request->status === 'disetujui')
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-800 space-y-3">
                            <p class="font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">info</span> Verifikasi & Pemotongan Stok
                            </p>
                            <p class="font-medium">
                                Untuk menyerahkan barang ke karyawan, klik tombol di bawah. Sistem akan memotong stok fisik secara real-time. Jika salah satu stok tidak mencukupi, operasi dibatalkan.
                            </p>
                            <form action="{{ route('atk.requests.process', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 font-semibold text-xs flex items-center gap-1.5 hover:bg-blue-700 transition-colors shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">outbox</span> Serahkan & Potong Stok
                                </button>
                            </form>
                        </div>
                    @elseif($request->status === 'diproses')
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-xs text-indigo-800 space-y-3">
                            <p class="font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">handshake</span> Penyelesaian Penyerahan
                            </p>
                            <p class="font-medium">
                                Barang telah dipotong dari stok dan diserahkan ke karyawan. Klik tombol di bawah untuk menandai status permintaan ini sebagai "Selesai".
                            </p>
                            <form action="{{ route('atk.requests.complete', $request->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-[#2563EB] text-white rounded px-4 py-2 font-semibold text-xs flex items-center gap-1.5 hover:bg-[#1D4ED8] transition-colors shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">task_alt</span> Selesaikan Permintaan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Sidebar (Timeline History) -->
        <div class="space-y-6">
            <div class="bg-white border border-[#E5E7EB] rounded-xl p-6 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-[#0F172A] border-b border-[#E5E7EB] pb-2 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[20px]">history</span> Riwayat Status
                </h3>

                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($request->histories as $index => $history)
                            <li>
                                <div class="relative pb-8">
                                    @if($index !== $request->histories->count() - 1)
                                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex items-start gap-3">
                                        <div class="shrink-0">
                                            @php
                                                $icons = [
                                                    'diajukan' => ['icon' => 'add_comment', 'color' => 'bg-indigo-500 text-white'],
                                                    'disetujui' => ['icon' => 'check_circle', 'color' => 'bg-green-500 text-white'],
                                                    'ditolak' => ['icon' => 'cancel', 'color' => 'bg-red-500 text-white'],
                                                    'diproses' => ['icon' => 'local_shipping', 'color' => 'bg-blue-500 text-white'],
                                                    'selesai' => ['icon' => 'check', 'color' => 'bg-slate-500 text-white'],
                                                    'dibatalkan' => ['icon' => 'cancel', 'color' => 'bg-amber-500 text-white'],
                                                ];
                                                $ic = $icons[$history->status] ?? ['icon' => 'info', 'color' => 'bg-slate-400 text-white'];
                                            @endphp
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center {{ $ic['color'] }} ring-8 ring-white">
                                                <span class="material-symbols-outlined text-[16px]">{{ $ic['icon'] }}</span>
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0 pt-0.5">
                                            <p class="text-xs font-bold text-slate-800">{{ ucfirst($history->status) }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium mt-0.5">Oleh {{ $history->actor->name }} ({{ $history->created_at->format('d M H:i') }})</p>
                                            <p class="text-[11px] text-slate-600 italic mt-1 bg-slate-50 p-1.5 rounded border border-slate-100">"{{ $history->notes }}"</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleRejectionBox() {
        const box = document.getElementById('rejection-box');
        if (box.classList.contains('hidden')) {
            box.classList.remove('hidden');
            document.getElementById('rejection_reason').focus();
        } else {
            box.classList.add('hidden');
        }
    }
</script>
@endsection
