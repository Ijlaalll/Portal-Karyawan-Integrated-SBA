@extends('layouts.app')

@section('title', 'Dashboard - SBA')

@section('content')
<div class="mx-auto max-w-7xl">
    <header class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-y-4">
        <div>
            <h1 class="text-3xl font-bold leading-tight tracking-tight text-[#0F172A]">
                Selamat datang kembali, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-[#475569] mt-1">Berikut adalah ringkasan dari pemesanan ruangan Anda.</p>
        </div>
        <div class="flex gap-x-3">
            <a href="{{ route('bookings.create') }}" class="rounded bg-[#2563EB] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8]">
                Pesan Ruangan
            </a>
        </div>
    </header>

    <main>
        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 sm:gap-5 mb-8">
            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg">
                <span class="text-xs font-semibold text-[#475569] uppercase tracking-wider block">Total Pemesanan</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['total'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-green-500">
                <span class="text-xs font-semibold text-green-800 uppercase tracking-wider block">Disetujui</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['approved'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-red-500">
                <span class="text-xs font-semibold text-red-800 uppercase tracking-wider block">Dibatalkan</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['cancelled'] }}</span>
            </div>

            <div class="bg-white p-5 border border-[#E5E7EB] rounded-lg border-l-4 border-l-blue-500">
                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider block">Selesai</span>
                <span class="text-3xl font-bold text-[#0F172A] mt-2 block">{{ $stats['completed'] }}</span>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="bg-white border border-[#E5E7EB] sm:rounded-lg overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-[#E5E7EB] bg-[#F9FAFB] flex justify-between items-center">
                <h3 class="text-base font-bold text-[#0F172A]">Pemesanan Terbaru</h3>
                <a href="{{ route('bookings.index') }}" class="text-xs font-semibold text-[#2563EB] hover:text-[#1D4ED8]">Lihat Semua</a>
            </div>
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
                        @forelse ($recentBookings as $booking)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-6 py-4.5 whitespace-nowrap font-bold text-[#0F172A]">{{ $booking->room ? $booking->room->name : 'Online' }}</td>
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
                            <td colspan="6" class="px-6 py-8 text-center text-[#475569] italic bg-white">
                                Tidak ada aktivitas pemesanan terbaru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
    </main>
</div>
@endsection
