@extends('layouts.app')

@section('title', 'Masuk - Portal SBA')

@section('content')
<div class="min-h-screen flex bg-white">
    <!-- Left panel: Signature Corporate SBA Blue (#1E3A8A) with Engineering Blueprint Texture -->
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center p-12 xl:p-16 overflow-hidden" 
        style="background-color: #1E3A8A;">
        
        <!-- Architectural / Engineering Grid Micro-Texture Overlay -->
        <div class="absolute inset-0 pointer-events-none opacity-20" 
            style="background-image: linear-gradient(to right, rgba(255, 255, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.15) 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        <!-- Subtle Ambient Lighting Glow -->
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] rounded-full pointer-events-none" 
            style="background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, transparent 65%);"></div>

        <div class="relative z-10 text-white max-w-md space-y-8 xl:space-y-10">
            <!-- Corporate Brand Mark -->
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold text-blue-100 border border-blue-400/30 bg-blue-900/40 mb-6 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-300"></span>
                    PORTAL KARYAWAN INTEGRATED
                </div>
                <h1 class="text-4xl font-bold tracking-tight text-white leading-tight">
                    PT. Solusi Bangun Andalas
                </h1>
                <p class="text-sm text-blue-100/90 mt-3 leading-relaxed">
                    Akses terpadu operasional pabrik: reservasi ruang meeting, inspeksi kebersihan toilet, manajemen ATK, surat izin keluar (SIK), serta manajemen kendaraan operasional.
                </p>
            </div>

            <!-- Clean Elegant List (No Cluttered Outline Boxes) -->
            <div class="space-y-3.5 xl:space-y-4 pt-6" style="border-top: 1px solid rgba(255, 255, 255, 0.15);">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-blue-300 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Manajemen Ruang Meeting</h4>
                        <p class="text-[11px] text-blue-100/80 mt-0.5">Pemesanan fasilitas, kalender reservasi, dan ulasan ruangan.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-blue-300 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Inspeksi Kebersihan Toilet</h4>
                        <p class="text-[11px] text-blue-100/80 mt-0.5">Checklist penilaian toilet area operasional pabrik.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-blue-300 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Pengadaan & Permintaan ATK</h4>
                        <p class="text-[11px] text-blue-100/80 mt-0.5">Pengajuan kebutuhan alat tulis kantor dan stock opname.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-blue-300 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Surat Izin Keluar (SIK)</h4>
                        <p class="text-[11px] text-blue-100/80 mt-0.5">Alur persetujuan HOD & validasi QR Code di pos security.</p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-blue-300 mt-0.5">check_circle</span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Manajemen Kendaraan Operasional</h4>
                        <p class="text-[11px] text-blue-100/80 mt-0.5">Permohonan armada kendaraan, penugasan supir, dan monitoring konsumsi BBM.</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 text-[11px] text-blue-200/70 font-medium">
                © {{ date('Y') }} PT. Solusi Bangun Andalas. Hak Cipta Dilindungi.
            </div>
        </div>
    </div>

    <!-- Right panel: Clean Login Form Container with Matching Engineering Grid Texture -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-12 lg:px-20 relative overflow-hidden" 
        style="background-color: #F8FAFC;">
        
        <!-- Crisp Engineering Grid Pattern Texture on Light Panel -->
        <div class="absolute inset-0 pointer-events-none" 
            style="background-image: linear-gradient(to right, #E2E8F0 1px, transparent 1px), linear-gradient(to bottom, #E2E8F0 1px, transparent 1px); background-size: 28px 28px; opacity: 0.85;"></div>
        
        <!-- Soft Ambient Vignette Overlay -->
        <div class="absolute inset-0 pointer-events-none"
            style="background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.6) 0%, rgba(248, 250, 252, 0.95) 100%);"></div>

        <div class="mx-auto w-full max-w-sm lg:w-[420px] bg-white p-6 sm:p-10 rounded-2xl shadow-xl space-y-6 relative z-10" 
            style="border: 1px solid #CBD5E1;">
            
            <!-- Company Header Branding -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#2563EB] flex items-center justify-center text-white font-extrabold text-lg shadow-md shadow-blue-500/20">
                    SBA
                </div>
                <div>
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">PT. Solusi Bangun Andalas</h2>
                    <p class="text-[11px] text-slate-500">Single Sign-On (SSO)</p>
                </div>
            </div>

            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-[#0F172A]">Selamat Datang Kembali</h1>
                <p class="mt-1 text-xs text-slate-500">Masukkan akun Anda untuk melanjutkan ke dashboard</p>
            </div>

            @if ($errors->any())
                <div class="rounded-xl bg-red-50 p-4 border border-red-200 text-xs space-y-1">
                    <div class="flex items-center gap-2 text-red-800 font-bold">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        Gagal Masuk
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="text-red-700 pl-6">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="space-y-4" action="{{ url('/login') }}" method="POST" id="login-form" onsubmit="setLoadingState();">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            placeholder="nama@domain.com"
                            class="block w-full rounded-xl pl-10 pr-3.5 py-2.5 text-xs text-[#0F172A] bg-white focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-all"
                            style="border: 1px solid #CBD5E1;">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kata Sandi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-xs">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <span class="material-symbols-outlined text-[18px]">lock</span>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="block w-full rounded-xl pl-10 pr-11 py-2.5 text-xs text-[#0F172A] bg-white focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#2563EB] transition-all"
                            style="border: 1px solid #CBD5E1;">
                        <button type="button" onclick="togglePasswordVisibility();" 
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-[#2563EB] transition-colors focus:outline-none cursor-pointer"
                            title="Tampilkan / Sembunyikan Kata Sandi">
                            <span class="material-symbols-outlined text-[18px]" id="password-toggle-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label for="remember" class="inline-flex items-center gap-2 text-xs text-slate-600 font-medium cursor-pointer">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-[#2563EB] focus:ring-[#2563EB] transition-all cursor-pointer">
                        Ingat saya
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" id="submit-button"
                        class="flex w-full justify-center items-center gap-2 rounded-xl py-3 px-4 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition-all cursor-pointer"
                        style="background-color: #2563EB;">
                        <span class="material-symbols-outlined text-[18px]" id="button-icon">login</span>
                        <span id="button-text">Masuk ke Portal</span>
                        <span id="button-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                    </button>
                </div>
            </form>

            <div class="pt-6 border-t border-slate-100 text-center">
                <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5 font-medium">
                    <span class="material-symbols-outlined text-[14px]">shield_lock</span>
                    Terenkripsi & Dilindungi Sistem Keamanan SBA
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0px 1000px #FFFFFF inset !important;
        -webkit-text-fill-color: #0F172A !important;
    }
</style>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('password-toggle-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = 'visibility';
        }
    }

    function setLoadingState() {
        const button = document.getElementById('submit-button');
        const text = document.getElementById('button-text');
        const icon = document.getElementById('button-icon');
        const spinner = document.getElementById('button-spinner');
        
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        text.textContent = 'Memproses Masuk...';
        if (icon) icon.classList.add('hidden');
        spinner.classList.remove('hidden');
    }
</script>
@endsection

