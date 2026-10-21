<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Akses Ditolak | SBA System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/ga_one_icon.png') }}">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0F172A] text-slate-100 font-sans flex items-center justify-center p-4 sm:p-6 antialiased">
    <div class="w-full max-w-lg bg-slate-800/80 border border-slate-700/80 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-2xl text-center">
        <!-- Shield Lock Icon -->
        <div class="mx-auto w-16 h-16 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center justify-center mb-5 shadow-inner">
            <span class="material-symbols-outlined text-[36px]">shield_lock</span>
        </div>

        <!-- Status Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 mb-4">
            <span>Error 403</span>
            <span>•</span>
            <span>Akses Ditolak</span>
        </div>

        <!-- Title -->
        <h1 class="text-xl sm:text-2xl font-bold text-white mb-3">
            Akses Tidak Diizinkan
        </h1>

        <!-- Exception Message -->
        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed mb-6 bg-slate-900/60 rounded-xl p-4 border border-slate-700/60 font-medium">
            {{ $exception->getMessage() ?: 'Akses ditolak: Anda tidak memiliki hak akses atau identitas yang lengkap untuk mengakses halaman ini.' }}
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <button type="button" onclick="window.history.back()" 
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-semibold text-xs sm:text-sm transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Kembali
            </button>
            <a href="{{ url('/') }}" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold text-xs sm:text-sm transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">home</span>
                Ke Dashboard Utama
            </a>
        </div>
    </div>
</body>
</html>
