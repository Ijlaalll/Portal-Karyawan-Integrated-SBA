@extends('layouts.app')

@section('title', 'Beranda Admin - SBA')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center space-y-2 mb-12">
        <h1 class="text-4xl font-bold tracking-tight text-[#0F172A]">
            Selamat datang kembali, Admin Sistem
        </h1>
        <p class="text-lg text-[#475569]">
            Pilih area manajemen untuk melanjutkan.
        </p>
    </div>

    @if(session('error'))
        <div class="mb-8 rounded-md bg-amber-50 p-4 border border-amber-200 text-sm text-amber-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Card 1: Meeting Room -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-8 shadow-sm flex flex-col justify-between hover:border-[#2563EB] hover:shadow-md transition-all">
            <div class="space-y-4">
                <div class="h-12 w-12 rounded-lg bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">meeting_room</span>
                </div>
                <h2 class="text-xl font-bold text-[#0F172A]">Manajemen Ruang Meeting</h2>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Kelola ruang meeting, pemesanan, fasilitas, pengguna, kalender, dan persetujuan.
                </p>
            </div>
            <div class="mt-8">
                <form action="{{ route('admin.select_module') }}" method="POST">
                    @csrf
                    <input type="hidden" name="module" value="meeting_room">
                    <button type="submit" class="w-full text-center rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
                        Buka Manajemen
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 2: Toilet Inspection -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-8 shadow-sm flex flex-col justify-between hover:border-[#2563EB] hover:shadow-md transition-all">
            <div class="space-y-4">
                <div class="h-12 w-12 rounded-lg bg-[#FFFBEB] text-[#D97706] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">wc</span>
                </div>
                <h2 class="text-xl font-bold text-[#0F172A]">Manajemen Inspeksi Toilet</h2>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Kelola lokasi inspeksi toilet, inspeksi, dan persetujuan inspeksi.
                </p>
            </div>
            <div class="mt-8">
                <form action="{{ route('admin.select_module') }}" method="POST">
                    @csrf
                    <input type="hidden" name="module" value="toilet_inspection">
                    <button type="submit" class="w-full text-center rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
                        Buka Manajemen
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 3: ATK -->
        <div class="bg-white border border-[#E5E7EB] rounded-lg p-6 sm:p-8 shadow-sm flex flex-col justify-between hover:border-[#2563EB] hover:shadow-md transition-all">
            <div class="space-y-4">
                <div class="h-12 w-12 rounded-lg bg-green-50 text-green-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[32px]">inventory_2</span>
                </div>
                <h2 class="text-xl font-bold text-[#0F172A]">Manajemen ATK</h2>
                <p class="text-sm text-[#475569] leading-relaxed">
                    Kelola alat tulis kantor (ATK), katalog, stok, dan pengajuan request.
                </p>
            </div>
            <div class="mt-8">
                <form action="{{ route('admin.select_module') }}" method="POST">
                    @csrf
                    <input type="hidden" name="module" value="atk">
                    <button type="submit" class="w-full text-center rounded bg-[#2563EB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#1D4ED8] transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
                        Buka Manajemen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
