@extends('layouts.app')

@section('title', 'Pemesanan Saya - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
            Pemesanan Saya
        </h1>
        <a href="{{ route('bookings.create') }}" 
            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm cursor-pointer"
            style="background-color: #2563EB;">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            Pesan Ruangan
        </a>
    </header>

    <main>
        @if (session('success'))
            <div class="mb-6 rounded-md p-4 text-sm font-medium" style="background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-[#E5E7EB] rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5E7EB]">
                    <thead class="bg-[#F9FAFB]">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Ruangan</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Judul</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-[#475569] uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-[#E5E7EB] text-[#0F172A] text-sm">
                        @forelse ($bookings as $booking)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4.5 whitespace-nowrap font-bold text-[#2563EB]">{{ $booking->room ? $booking->room->name : 'Online' }}</td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-[#475569] font-medium">{{ $booking->title }}</td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-[#0F172A] font-medium">{{ $booking->booking_date->format('d M Y') }}</td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-[#475569] font-mono text-xs">
                                    {{ date('H:i', strtotime($booking->start_time)) }} - {{ date('H:i', strtotime($booking->end_time)) }}
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap">
                                    @php
                                        $statusStyles = [
                                            'pending' => 'background-color: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;',
                                            'approved' => 'background-color: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;',
                                            'rejected' => 'background-color: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;',
                                            'cancelled' => 'background-color: #F8FAFC; color: #64748B; border: 1px solid #CBD5E1;',
                                            'completed' => 'background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;',
                                        ];
                                        $statusStyle = $statusStyles[$booking->status] ?? 'background-color: #F8FAFC; color: #64748B; border: 1px solid #CBD5E1;';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold" style="{{ $statusStyle }}">
                                        {{ $booking->status === 'pending' ? 'Menunggu' : ($booking->status === 'approved' ? 'Disetujui' : ($booking->status === 'rejected' ? 'Ditolak' : ($booking->status === 'completed' ? 'Selesai' : ($booking->status === 'cancelled' ? 'Dibatalkan' : ucfirst($booking->status))))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-right text-xs font-semibold">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('bookings.show', $booking) }}" 
                                            class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold hover:opacity-80 transition-opacity"
                                            style="background-color: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                                            <span class="material-symbols-outlined text-[15px]">visibility</span>
                                            Lihat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-[#475569] italic bg-white">
                                    Anda belum membuat permintaan pemesanan apa pun.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bookings->hasPages())
                <div class="px-6 py-4 border-t border-[#E5E7EB]">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
