@extends('layouts.app')

@section('title', 'Tidak Ada Akses - SBA')

@section('content')
<div class="max-w-md mx-auto py-16 px-4 sm:px-6 lg:px-8">
    <div class="bg-white border border-[#E5E7EB] rounded-lg p-8 shadow-sm text-center space-y-6">
        <div class="h-16 w-16 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
            <span class="material-symbols-outlined text-[36px]">gpp_bad</span>
        </div>
        
        <div class="space-y-2">
            <h1 class="text-2xl font-bold text-[#0F172A]">Tidak Ada Akses Manajemen</h1>
            <p class="text-sm text-[#475569] leading-relaxed">
                Akun Anda terautentikasi, tetapi saat ini Anda tidak memiliki akses ke modul manajemen apa pun. Silakan hubungi administrator untuk meminta akses.
            </p>
        </div>

        <div class="pt-4 border-t border-[#E5E7EB]">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full text-center rounded border border-[#D1D5DB] bg-white px-4 py-2.5 text-sm font-semibold text-[#374151] hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-[#2563EB] focus:ring-offset-2">
                    Keluar dan Kembali ke Halaman Masuk
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
