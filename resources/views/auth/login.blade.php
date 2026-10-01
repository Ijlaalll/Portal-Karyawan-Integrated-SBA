@extends('layouts.app')

@section('title', 'Masuk - GA One | Portal Karyawan Terintegrasi SBA')

@section('content')
<div class="min-h-screen w-full relative flex items-center justify-center p-4 sm:p-6 lg:p-12 overflow-x-hidden font-sans">
    <!-- 1. Cinematic Factory Background (Aerial View of SBA Plant Lhoknga) -->
    <div class="fixed inset-0 bg-cover bg-center bg-no-repeat z-0 transform scale-105 transition-transform duration-1000 ease-out"
         style="background-image: url('{{ asset('images/sba-factory-aerial.jpg') }}'); filter: brightness(0.7) saturate(1.15);">
    </div>

    <!-- 2. Dark Atmospheric Cyan & Navy Blueprint Vignette Overlay (Matches Linda Reference) -->
    <div class="fixed inset-0 z-0 bg-gradient-to-r from-[#061224]/95 via-[#0A1A33]/85 to-[#08152B]/92 pointer-events-none"></div>
    
    <!-- 3. Ambient Radial Glow Lights -->
    <div class="fixed inset-0 z-0 pointer-events-none" 
         style="background: radial-gradient(circle at 18% 45%, rgba(6, 182, 212, 0.22) 0%, transparent 55%), radial-gradient(circle at 82% 25%, rgba(37, 99, 235, 0.20) 0%, transparent 50%), radial-gradient(circle at 50% 90%, rgba(16, 185, 129, 0.15) 0%, transparent 60%);">
    </div>

    <!-- 4. Subtle Engineering Coordinate Grid -->
    <div class="fixed inset-0 z-0 pointer-events-none opacity-10" 
         style="background-image: linear-gradient(to right, rgba(255, 255, 255, 0.15) 1px, transparent 1px), linear-gradient(to bottom, rgba(255, 255, 255, 0.15) 1px, transparent 1px); background-size: 36px 36px;">
    </div>

    <!-- 5. Main Content Container -->
    <div class="max-w-7xl w-full mx-auto relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center py-6 sm:py-8">
        
        <!-- ========================================== -->
        <!-- LEFT COLUMN: Brand Hero & Network Nodes   -->
        <!-- ========================================== -->
        <div class="col-span-12 lg:col-span-7 space-y-8 relative">
            
            <!-- Floating Network Graphic Overlay (SVG Constellation) -->
            <div class="relative max-w-lg">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold text-[#38BDF8] border border-cyan-500/30 bg-[#0C2340]/70 backdrop-blur-md shadow-lg shadow-cyan-950/40">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                    <span class="tracking-wide">PORTAL INTEGRASI KARYAWAN SBA</span>
                </div>

                <!-- Main Hero Typography -->
                <h1 class="text-4xl sm:text-5xl lg:text-[56px] font-extrabold text-white tracking-tight leading-[1.12] mt-6">
                    Connecting services<br>
                    into <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#10B981] via-[#06B6D4] to-[#38BDF8]">One Place.</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-300 font-medium tracking-wide mt-4 flex items-center gap-2">
                    <span class="text-[#38BDF8] font-bold">#Growing together.</span>
                    <span class="text-slate-500">•</span>
                    <span class="text-slate-300">PT Solusi Bangun Andalas</span>
                </p>
            </div>

            <!-- Operational Feature Badges (Interactive 5-Module Network) -->
            <div class="pt-4 max-w-xl">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#38BDF8]">meeting_room</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Ruang Meeting</p>
                            <p class="text-[10px] text-slate-400">Offline & Hybrid</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#34D399]">inventory_2</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Logistik ATK</p>
                            <p class="text-[10px] text-slate-400">Katalog & Stok</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#F59E0B]">badge</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Izin Keluar (SIK)</p>
                            <p class="text-[10px] text-slate-400">QR Code Security</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#60A5FA]">directions_car</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Kendaraan</p>
                            <p class="text-[10px] text-slate-400">Armada & Supir</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#A78BFA]">wc</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Inspeksi Toilet</p>
                            <p class="text-[10px] text-slate-400">Checklist Pabrik</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-slate-900/60 hover:bg-slate-900/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 backdrop-blur-md transition-all duration-200">
                        <span class="material-symbols-outlined text-[19px] text-[#38BDF8]">verified_user</span>
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">Single Sign-On</p>
                            <p class="text-[10px] text-slate-400">Akun Terpadu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- RIGHT COLUMN: Floating Glassmorphism Card  -->
        <!-- ========================================== -->
        <div class="col-span-12 lg:col-span-5 flex justify-center lg:justify-end">
            <div class="w-full max-w-[440px] bg-[#0B1528]/85 backdrop-blur-2xl border border-white/12 rounded-3xl p-7 sm:p-9 shadow-2xl relative overflow-hidden"
                 style="box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6), 0 0 35px -8px rgba(6, 182, 212, 0.25);">
                
                <!-- Glowing Top Accent Line (Linda Style) -->
                <div class="absolute top-0 inset-x-0 h-[2px] bg-gradient-to-r from-transparent via-[#38BDF8] to-transparent"></div>

                <!-- Brand Logo & Header -->
                <div class="text-center mb-6">
                    <img src="{{ asset('images/ga_one_logo_white.png') }}" alt="GA One" class="h-11 sm:h-12 w-auto mx-auto object-contain drop-shadow-md">
                    
                    <p class="text-[12px] font-medium text-slate-300 tracking-wide mt-2.5">
                        Integrated <span class="text-[#34D399] font-bold">Monitoring</span> &amp; <span class="text-[#38BDF8] font-bold">Service</span> System App
                    </p>
                    <p class="text-xs text-slate-400 mt-1 font-medium">Sign in to continue</p>
                </div>

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-red-950/60 p-3.5 border border-red-500/40 text-xs space-y-1">
                        <div class="flex items-center gap-2 text-red-300 font-bold">
                            <span class="material-symbols-outlined text-[17px]">error</span>
                            Gagal Masuk
                        </div>
                        @foreach ($errors->all() as $error)
                            <p class="text-red-200/90 pl-6 leading-relaxed">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form -->
                <form class="space-y-4" action="{{ url('/login') }}" method="POST" id="login-form" onsubmit="setLoadingState();">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Email
                        </label>
                        <div class="relative rounded-xl shadow-xs group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#38BDF8] transition-colors">
                                <span class="material-symbols-outlined text-[19px]">mail</span>
                            </div>
                            <input id="email" name="email" type="text" autocomplete="username" required value="{{ old('email') }}"
                                placeholder="Enter email"
                                class="block w-full rounded-xl pl-11 pr-4 py-3 text-sm text-white bg-slate-900/70 border border-slate-700/80 placeholder-slate-500 focus:bg-slate-900/95 focus:outline-none focus:ring-2 focus:ring-[#38BDF8] focus:border-transparent transition-all">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Password
                        </label>
                        <div class="relative rounded-xl shadow-xs group">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#38BDF8] transition-colors">
                                <span class="material-symbols-outlined text-[19px]">lock</span>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                placeholder="Enter password"
                                class="block w-full rounded-xl pl-11 pr-12 py-3 text-sm text-white bg-slate-900/70 border border-slate-700/80 placeholder-slate-500 focus:bg-slate-900/95 focus:outline-none focus:ring-2 focus:ring-[#38BDF8] focus:border-transparent transition-all">
                            
                            <button type="button" onclick="togglePasswordVisibility();" 
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-[#38BDF8] transition-colors focus:outline-none cursor-pointer"
                                title="Tampilkan / Sembunyikan Kata Sandi">
                                <span class="material-symbols-outlined text-[20px]" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember" class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-900/80 text-[#2563EB] focus:ring-[#38BDF8] focus:ring-offset-0 cursor-pointer">
                            <span>Remember me</span>
                        </label>
                        
                        <button type="button" onclick="showForgotPasswordModal();" 
                            class="text-xs font-medium text-[#38BDF8] hover:text-cyan-300 transition-colors cursor-pointer">
                            Forgot password?
                        </button>
                    </div>

                    <!-- Submit Button: Gradient Luminous Style matching Linda -->
                    <div class="pt-2">
                        <button type="submit" id="submit-button"
                            class="flex w-full justify-center items-center gap-2 rounded-xl py-3.5 px-4 text-sm font-bold text-white bg-gradient-to-r from-[#10B981] via-[#06B6D4] to-[#2563EB] hover:from-[#059669] hover:via-[#0891B2] hover:to-[#1D4ED8] shadow-lg shadow-cyan-900/30 hover:shadow-cyan-500/25 transition-all duration-300 hover:scale-[1.01] active:scale-[0.99] cursor-pointer">
                            <span id="button-text">Log in</span>
                            <span class="material-symbols-outlined text-[19px] shrink-0" id="button-icon">arrow_forward</span>
                            <span id="button-spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                        </button>
                    </div>
                </form>

                <!-- Separator -->
                <div class="relative flex items-center justify-center my-5">
                    <div class="border-t border-slate-800 w-full"></div>
                    <span class="bg-[#0B1528] px-3 text-[11px] text-slate-400 uppercase tracking-wider shrink-0 font-medium">or continue with</span>
                    <div class="border-t border-slate-800 w-full"></div>
                </div>

                <!-- SSO Button (Single Sign-On SIG) -->
                <div>
                    <button type="button" onclick="document.getElementById('email').focus();"
                        class="w-full flex items-center justify-between px-4 py-3 bg-slate-800/60 hover:bg-slate-800/90 border border-slate-700/80 rounded-xl text-xs font-semibold text-slate-200 hover:text-white transition-all cursor-pointer shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-[#38BDF8]">verified_user</span>
                            <span>SSO / Single Sign-On</span>
                        </div>
                        <span class="material-symbols-outlined text-[16px] text-slate-400">chevron_right</span>
                    </button>
                </div>

                <!-- Footer Badges -->
                <div class="pt-6 border-t border-slate-800/80 text-center space-y-2">
                    <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5 font-medium">
                        <span class="material-symbols-outlined text-[15px] text-[#34D399]">shield</span>
                        Secure. Reliable. Connected.
                    </p>
                    <p class="text-[10px] text-slate-500 tracking-wide uppercase font-semibold">
                        &copy; {{ date('Y') }} PT SOLUSI BANGUN ANDALAS
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bantuan Lupa Password -->
<div id="forgot-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
    <div class="bg-[#0F172A] border border-slate-700 rounded-2xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl">
        <div class="w-12 h-12 rounded-full bg-cyan-950/70 border border-cyan-500/40 text-[#38BDF8] flex items-center justify-center mx-auto">
            <span class="material-symbols-outlined text-[24px]">support_agent</span>
        </div>
        <div>
            <h3 class="text-base font-bold text-white">Reset Kata Sandi Akun</h3>
            <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                Akun portal ini terintegrasi dengan kredensial Single Sign-On (SSO). Untuk mengatur ulang kata sandi atau kendala akun, silakan hubungi <strong>Helpdesk IT SBA</strong> atau Administrator Sistem.
            </p>
        </div>
        <button type="button" onclick="closeForgotPasswordModal();"
            class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-[#2563EB] hover:bg-blue-600 transition-colors cursor-pointer">
            Mengerti &amp; Tutup
        </button>
    </div>
</div>

<style>
    /* Prevent browser white autofill background override in dark theme */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0px 1000px #0F172A inset !important;
        -webkit-text-fill-color: #FFFFFF !important;
        border-color: #38BDF8 !important;
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

    function showForgotPasswordModal() {
        document.getElementById('forgot-modal').classList.remove('hidden');
    }

    function closeForgotPasswordModal() {
        document.getElementById('forgot-modal').classList.add('hidden');
    }
</script>
@endsection
