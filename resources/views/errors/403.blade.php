<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Dibatasi (403) - SBA Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-[#0F172A] bg-gradient-to-b from-slate-50 via-blue-50/20 to-slate-100">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200/80 p-6 sm:p-8 text-center relative overflow-hidden">
        <!-- Top Accent Bar -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-amber-500 via-rose-500 to-blue-600"></div>

        <!-- Shield Icon -->
        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center mx-auto mb-4 sm:mb-5 shadow-sm">
            <span class="material-symbols-outlined text-[36px] sm:text-[44px]">shield_lock</span>
        </div>

        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-black tracking-wider uppercase bg-amber-100/80 text-amber-800 mb-2">
            Kode Kesalahan: 403
        </span>

        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mb-2">
            Akses Dibatasi
        </h1>

        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses atau identitas yang cukup untuk membuka halaman ini.' }}
        </p>

        <!-- Action Buttons -->
        <div class="space-y-2.5">
            @auth
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 transition-all active:scale-98">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Kembali ke Halaman Sebelumnya
                </a>
                <a href="{{ url('/') }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs sm:text-sm font-bold transition-all">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Menu Utama / Beranda
                </a>
            @else
                <a href="{{ route('login') }}" 
                   class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs sm:text-sm font-bold shadow-md shadow-blue-500/20 transition-all active:scale-98">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    Masuk ke Akun Anda
                </a>
            @endauth
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 text-[11px] text-slate-400">
            PT. Solusi Bangun Andalas &bull; Portal Operasional Terpadu
        </div>
    </div>
</body>
</html>
