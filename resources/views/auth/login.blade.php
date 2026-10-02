@extends('layouts.app')

@section('title', 'Masuk - GA One | Portal Karyawan Terintegrasi SBA')

@section('content')
<!-- Container Utama Halaman Login -->
<div class="linda-login-wrapper">

    <!-- 1. Cinematic Factory Background (Full Viewport SBA Plant - Color Graded) -->
    <div class="linda-bg-cinematic"></div>

    <!-- 2. Dark Atmospheric Cyan & Navy Overlay (Identik dengan Referensi Linda) -->
    <div class="linda-overlay-dark"></div>

    <!-- 3. Ambient Radial Lighting (Cyan, Emerald, Royal Blue Glows) -->
    <div class="linda-ambient-glows"></div>

    <!-- 4. Constellation Network Nodes Overlay (Persis seperti pada latar gunung Linda) -->
    <div class="linda-constellation-container">
        <!-- SVG Constellation Mesh di Kiri Bawah -->
        <svg class="linda-constellation-svg" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Garis Jaringan -->
            <line x1="40" y1="520" x2="110" y2="440" stroke="rgba(6, 182, 212, 0.3)" stroke-width="1.5" />
            <line x1="110" y1="440" x2="90" y2="350" stroke="rgba(6, 182, 212, 0.25)" stroke-width="1.5" />
            <line x1="110" y1="440" x2="210" y2="470" stroke="rgba(16, 185, 129, 0.3)" stroke-width="1.5" />
            <line x1="90" y1="350" x2="20" y2="280" stroke="rgba(6, 182, 212, 0.2)" stroke-width="1.5" />
            <line x1="210" y1="470" x2="290" y2="400" stroke="rgba(6, 182, 212, 0.25)" stroke-width="1.5" />
            <line x1="90" y1="350" x2="290" y2="400" stroke="rgba(56, 189, 248, 0.2)" stroke-width="1.2" stroke-dasharray="4 4" />
            <line x1="290" y1="400" x2="380" y2="460" stroke="rgba(6, 182, 212, 0.2)" stroke-width="1.2" stroke-dasharray="4 4" />

            <!-- Titik-titik Berpendar -->
            <circle cx="40" cy="520" r="4" fill="#06B6D4" />
            <circle cx="110" cy="440" r="5" fill="#10B981" />
            <circle cx="90" cy="350" r="4.5" fill="#38BDF8" />
            <circle cx="20" cy="280" r="3.5" fill="#06B6D4" />
            <circle cx="210" cy="470" r="4.5" fill="#34D399" />
            <circle cx="290" cy="400" r="5" fill="#38BDF8" />
            <circle cx="380" cy="460" r="3.5" fill="#06B6D4" />
        </svg>

        <!-- Node 1: DATA INTEGRATION (Kiri Tengah) -->
        <div class="linda-node-pin" style="top: 32%; left: 14%;">
            <span class="linda-pin-glow"></span>
            <span class="linda-pin-dot"></span>
            <span class="linda-pin-label">DATA INTEGRATION</span>
        </div>

        <!-- Node 2: MONITORING (Atas Tengah Pabrik) -->
        <div class="linda-node-pin" style="top: 24%; left: 44%;">
            <span class="linda-pin-glow"></span>
            <span class="linda-pin-dot"></span>
            <span class="linda-pin-label">MONITORING</span>
        </div>

        <!-- Node 3: OPERATIONAL (Tengah Bawah) -->
        <div class="linda-node-pin" style="top: 66%; left: 34%;">
            <span class="linda-pin-glow"></span>
            <span class="linda-pin-dot"></span>
            <span class="linda-pin-label">OPERATIONAL</span>
        </div>

        <!-- Node 4: SECURE (Kanan Bawah) -->
        <div class="linda-node-pin" style="top: 78%; left: 48%;">
            <span class="linda-pin-glow"></span>
            <span class="linda-pin-dot"></span>
            <span class="linda-pin-label">SECURE</span>
        </div>
    </div>

    <!-- 5. Konten Halaman (Grid 2 Kolom) -->
    <div class="linda-content-container">
        
        <!-- ========================================== -->
        <!-- KOLOM KIRI: Brand Hero Headline & Modules  -->
        <!-- ========================================== -->
        <div class="linda-left-hero">
            
            <!-- Badge Identitas Resmi -->
            <div class="linda-badge">
                <span class="linda-badge-pulse"></span>
                <span class="linda-badge-text">PORTAL INTEGRASI KARYAWAN SBA</span>
            </div>

            <!-- Headline Utama (Gaya Persis Linda) -->
            <h1 class="linda-hero-heading">
                Connecting services<br>
                into <span class="linda-gradient-text">One Place.</span>
            </h1>

            <p class="linda-hero-sub">
                <span class="linda-tag-blue">#Growing together.</span>
                <span class="linda-tag-dot">•</span>
                <span class="linda-tag-sub">PT Solusi Bangun Andalas</span>
            </p>

            <!-- 6 Modul Utama Layanan SBA -->
            <div class="linda-modules-grid">
                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#38BDF8]">meeting_room</span>
                    <div>
                        <div class="linda-mod-title">Ruang Meeting</div>
                        <div class="linda-mod-desc">Offline &amp; Hybrid</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#34D399]">inventory_2</span>
                    <div>
                        <div class="linda-mod-title">Logistik ATK</div>
                        <div class="linda-mod-desc">Katalog &amp; Stok</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#F59E0B]">badge</span>
                    <div>
                        <div class="linda-mod-title">Izin Keluar (SIK)</div>
                        <div class="linda-mod-desc">QR Code Gate</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#60A5FA]">directions_car</span>
                    <div>
                        <div class="linda-mod-title">Kendaraan</div>
                        <div class="linda-mod-desc">Armada &amp; Supir</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#A78BFA]">wc</span>
                    <div>
                        <div class="linda-mod-title">Inspeksi Toilet</div>
                        <div class="linda-mod-desc">Checklist Pabrik</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined text-[18px] text-[#38BDF8]">verified_user</span>
                    <div>
                        <div class="linda-mod-title">Single Sign-On</div>
                        <div class="linda-mod-desc">Akun Terpadu</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- KOLOM KANAN: Kartu Login Glassmorphism     -->
        <!-- ========================================== -->
        <div class="linda-right-card-wrapper">
            <div class="linda-card">
                
                <!-- Glowing Top Accent Line (Ciri Khas Kartu Linda) -->
                <div class="linda-card-top-accent"></div>

                <!-- Logo GA One & Subtitle Resmi -->
                <div class="linda-card-header">
                    <!-- Logo GA One White Versi HiDPI Crisp (Anti Crop-Artifacts) -->
                    <div class="linda-logo-box">
                        <img src="{{ asset('images/ga_one_logo_white.png') }}?v=2" alt="GA One" class="linda-logo-img">
                    </div>
                    
                    <p class="linda-card-subtitle">
                        Integrated <span class="text-[#34D399] font-bold">Monitoring</span> &amp; <span class="text-[#38BDF8] font-bold">Service</span> System App
                    </p>
                    <p class="linda-card-prompt">Sign in to continue</p>
                </div>

                <!-- Notifikasi Error Validasi -->
                @if ($errors->any())
                    <div class="linda-alert-error">
                        <div class="linda-alert-title">
                            <span class="material-symbols-outlined text-[16px]">error</span>
                            Gagal Masuk
                        </div>
                        @foreach ($errors->all() as $error)
                            <p class="linda-alert-msg">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Formulir Login -->
                <form class="linda-form" action="{{ url('/login') }}" method="POST" id="login-form" onsubmit="setLoadingState();">
                    @csrf

                    <!-- Email Field -->
                    <div class="linda-field-group">
                        <label for="email" class="linda-label">Email</label>
                        <div class="linda-input-wrap">
                            <span class="material-symbols-outlined linda-input-icon">mail</span>
                            <input id="email" name="email" type="text" autocomplete="username" required value="{{ old('email') }}"
                                placeholder="Enter email"
                                class="linda-input">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="linda-field-group">
                        <label for="password" class="linda-label">Password</label>
                        <div class="linda-input-wrap">
                            <span class="material-symbols-outlined linda-input-icon">lock</span>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                placeholder="Enter password"
                                class="linda-input linda-input-password">
                            
                            <button type="button" onclick="togglePasswordVisibility();" 
                                class="linda-pwd-toggle"
                                title="Tampilkan / Sembunyikan Kata Sandi">
                                <span class="material-symbols-outlined text-[20px]" id="password-toggle-icon">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me & Forgot Password -->
                    <div class="linda-options-row">
                        <label for="remember" class="linda-remember-label">
                            <input id="remember" name="remember" type="checkbox" class="linda-checkbox">
                            <span>Remember me</span>
                        </label>
                        
                        <button type="button" onclick="showForgotPasswordModal();" class="linda-forgot-btn">
                            Forgot password?
                        </button>
                    </div>

                    <!-- Tombol Log in Bergradasi (Persis Linda: Emerald -> Cyan -> Blue) -->
                    <div class="pt-2">
                        <button type="submit" id="submit-button" class="linda-submit-btn">
                            <span id="button-text">Log in</span>
                            <span class="material-symbols-outlined text-[19px]" id="button-icon">arrow_forward</span>
                            <span id="button-spinner" class="linda-spinner hidden"></span>
                        </button>
                    </div>
                </form>

                <!-- Separator 'or continue with' -->
                <div class="linda-separator">
                    <div class="linda-sep-line"></div>
                    <span class="linda-sep-text">or continue with</span>
                    <div class="linda-sep-line"></div>
                </div>

                <!-- Tombol SSO (Single Sign-On SIG) -->
                <div>
                    <button type="button" onclick="document.getElementById('email').focus();" class="linda-sso-btn">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[19px] text-[#38BDF8]">verified_user</span>
                            <span>SSO / Single Sign-On</span>
                        </div>
                        <span class="material-symbols-outlined text-[18px] text-slate-400">chevron_right</span>
                    </button>
                </div>

                <!-- Footer Kartu -->
                <div class="linda-card-footer">
                    <p class="linda-footer-badge">
                        <span class="material-symbols-outlined text-[15px] text-[#34D399]">shield</span>
                        Secure. Reliable. Connected.
                    </p>
                    <p class="linda-footer-copyright">
                        &copy; {{ date('Y') }} PT SOLUSI BANGUN ANDALAS
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bantuan Lupa Password -->
<div id="forgot-modal" class="linda-modal-overlay">
    <div class="linda-modal-box">
        <div class="linda-modal-icon-wrap">
            <span class="material-symbols-outlined text-[24px]">support_agent</span>
        </div>
        <div>
            <h3 class="text-base font-bold text-white">Reset Kata Sandi Akun</h3>
            <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                Akun portal ini terintegrasi dengan kredensial Single Sign-On (SSO). Untuk mengatur ulang kata sandi atau kendala akun, silakan hubungi <strong>Helpdesk IT SBA</strong> atau Administrator Sistem.
            </p>
        </div>
        <button type="button" onclick="closeForgotPasswordModal();" class="linda-modal-close-btn">
            Mengerti &amp; Tutup
        </button>
    </div>
</div>

<!-- ============================================================== -->
<!-- CSS Mandiri & Terisolasi Penuh (Mencegah Masalah Tailwind Build) -->
<!-- ============================================================== -->
<style>
    /* 1. Layout & Viewport Wrapper */
    .linda-login-wrapper {
        position: relative;
        min-height: 100vh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 1rem;
        overflow-x: hidden;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    /* 2. Full Viewport Aerial Factory Background */
    .linda-bg-cinematic {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 0 !important;
        background-image: url('{{ asset('images/sba-factory-aerial.jpg') }}?v=2') !important;
        background-size: cover !important;
        background-position: center center !important;
        background-repeat: no-repeat !important;
        filter: brightness(0.7) contrast(1.1) saturate(0.95);
        pointer-events: none;
    }

    /* 3. Dark Atmospheric Vignette Overlay */
    .linda-overlay-dark {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 1 !important;
        background: radial-gradient(circle at 75% 35%, rgba(10, 25, 52, 0.78) 0%, rgba(6, 14, 28, 0.94) 70%),
                    linear-gradient(135deg, rgba(6, 18, 38, 0.94) 0%, rgba(9, 22, 44, 0.85) 45%, rgba(5, 12, 24, 0.96) 100%) !important;
        pointer-events: none;
    }

    /* 4. Ambient Radial Glow Lights */
    .linda-ambient-glows {
        position: fixed;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background: radial-gradient(circle at 20% 50%, rgba(6, 182, 212, 0.22) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(37, 99, 235, 0.22) 0%, transparent 45%),
                    radial-gradient(circle at 50% 90%, rgba(16, 185, 129, 0.15) 0%, transparent 55%);
    }

    /* 5. Constellation Network Pins */
    .linda-constellation-container {
        position: fixed;
        inset: 0;
        z-index: 3;
        pointer-events: none;
    }

    .linda-constellation-svg {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 480px;
        height: 480px;
        opacity: 0.7;
    }

    .linda-node-pin {
        position: absolute;
        display: none;
        align-items: center;
        gap: 8px;
        transform: translate(-50%, -50%);
    }

    @media (min-width: 1024px) {
        .linda-node-pin {
            display: flex;
        }
    }

    .linda-pin-glow {
        position: absolute;
        width: 16px;
        height: 16px;
        border-radius: 9999px;
        background: rgba(6, 182, 212, 0.6);
        animation: linda-pulse 2.5s infinite ease-in-out;
    }

    .linda-pin-dot {
        position: relative;
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #00F2FE;
        box-shadow: 0 0 10px #00F2FE;
    }

    .linda-pin-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #38BDF8;
        background: rgba(6, 20, 42, 0.75);
        padding: 2px 7px;
        border-radius: 6px;
        border: 1px solid rgba(56, 189, 248, 0.3);
        backdrop-filter: blur(8px);
    }

    @keyframes linda-pulse {
        0%, 100% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(2.2); opacity: 0; }
    }

    /* 6. Content Grid */
    .linda-content-container {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 1240px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1fr;
        gap: 2.5rem;
        align-items: center;
    }

    @media (min-width: 1024px) {
        .linda-content-container {
            grid-template-columns: 1.15fr 0.85fr;
            gap: 3.5rem;
        }
    }

    /* 7. Left Hero Column */
    .linda-left-hero {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .linda-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #38BDF8;
        background: rgba(12, 35, 64, 0.85);
        border: 1px solid rgba(56, 189, 248, 0.35);
        backdrop-filter: blur(12px);
        width: fit-content;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.15);
    }

    .linda-badge-pulse {
        width: 8px;
        height: 8px;
        border-radius: 9999px;
        background: #38BDF8;
        box-shadow: 0 0 8px #38BDF8;
        animation: linda-pulse 2s infinite ease-in-out;
    }

    .linda-hero-heading {
        font-size: 2.5rem;
        line-height: 1.15;
        font-weight: 800;
        color: #FFFFFF;
        letter-spacing: -0.02em;
        margin: 0;
    }

    @media (min-width: 640px) {
        .linda-hero-heading { font-size: 3.25rem; }
    }

    @media (min-width: 1024px) {
        .linda-hero-heading { font-size: 3.6rem; }
    }

    .linda-gradient-text {
        background: linear-gradient(90deg, #10B981 0%, #06B6D4 50%, #38BDF8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .linda-hero-sub {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        margin: 0;
    }

    .linda-tag-blue { color: #38BDF8; font-weight: 700; }
    .linda-tag-dot { color: #64748B; }
    .linda-tag-sub { color: #CBD5E1; font-weight: 500; }

    /* Module Badges Grid */
    .linda-modules-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        max-width: 580px;
        padding-top: 0.5rem;
    }

    @media (min-width: 640px) {
        .linda-modules-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .linda-mod-pill {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(11, 22, 42, 0.7);
        border: 1px solid rgba(71, 85, 105, 0.5);
        border-radius: 14px;
        padding: 10px 14px;
        backdrop-filter: blur(12px);
        transition: all 0.2s ease;
    }

    .linda-mod-pill:hover {
        background: rgba(15, 30, 58, 0.9);
        border-color: rgba(56, 189, 248, 0.4);
        transform: translateY(-2px);
    }

    .linda-mod-title {
        font-size: 11px;
        font-weight: 700;
        color: #F8FAFC;
        line-height: 1.2;
    }

    .linda-mod-desc {
        font-size: 10px;
        color: #94A3B8;
        line-height: 1.2;
    }

    /* 8. Right Login Card (Solid Dark Glassmorphism, Crisp Contrast) */
    .linda-right-card-wrapper {
        display: flex;
        justify-content: center;
        width: 100%;
    }

    @media (min-width: 1024px) {
        .linda-right-card-wrapper {
            justify-content: flex-end;
        }
    }

    .linda-card {
        width: 100%;
        max-width: 440px;
        background: rgba(11, 20, 36, 0.92) !important;
        backdrop-filter: blur(28px) !important;
        -webkit-backdrop-filter: blur(28px) !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 26px !important;
        padding: 34px 34px 28px !important;
        box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.8),
                    0 0 40px -10px rgba(6, 182, 212, 0.25) !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .linda-card-top-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent 0%, #10B981 35%, #06B6D4 65%, transparent 100%);
    }

    .linda-card-header {
        text-align: center;
        margin-bottom: 1.6rem;
    }

    .linda-logo-box {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 0.65rem;
    }

    .linda-logo-img {
        height: 46px;
        width: auto;
        max-width: 220px;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
    }

    .linda-card-subtitle {
        font-size: 12px;
        font-weight: 500;
        color: #CBD5E1;
        letter-spacing: 0.02em;
        margin: 0;
    }

    .linda-card-prompt {
        font-size: 11px;
        color: #94A3B8;
        font-weight: 500;
        margin-top: 4px;
        margin-bottom: 0;
    }

    /* Error Alert */
    .linda-alert-error {
        margin-bottom: 1.25rem;
        background: rgba(127, 29, 29, 0.6);
        border: 1px solid rgba(239, 68, 68, 0.4);
        border-radius: 12px;
        padding: 10px 14px;
    }

    .linda-alert-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #FCA5A5;
    }

    .linda-alert-msg {
        font-size: 11px;
        color: #FECACA;
        padding-left: 22px;
        margin-top: 4px;
    }

    /* Form & Input Fields */
    .linda-form {
        display: flex;
        flex-direction: column;
        gap: 1.1rem;
    }

    .linda-field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .linda-label {
        font-size: 12px;
        font-weight: 600;
        color: #E2E8F0;
    }

    .linda-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .linda-input-icon {
        position: absolute;
        left: 14px;
        color: #94A3B8;
        font-size: 19px;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .linda-input-wrap:focus-within .linda-input-icon {
        color: #38BDF8;
    }

    .linda-input {
        width: 100% !important;
        background: rgba(8, 16, 28, 0.85) !important;
        border: 1px solid rgba(71, 85, 105, 0.7) !important;
        border-radius: 12px !important;
        padding: 12px 16px 12px 42px !important;
        font-size: 13.5px !important;
        color: #FFFFFF !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }

    .linda-input::placeholder {
        color: #64748B !important;
    }

    .linda-input:focus {
        background: rgba(8, 16, 28, 0.98) !important;
        border-color: #06B6D4 !important;
        box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.25) !important;
    }

    .linda-input-password {
        padding-right: 44px !important;
    }

    .linda-pwd-toggle {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: #94A3B8;
        cursor: pointer;
        display: flex;
        align-items: center;
        padding: 4px;
        transition: color 0.2s ease;
    }

    .linda-pwd-toggle:hover {
        color: #38BDF8;
    }

    /* Options Row */
    .linda-options-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 2px;
    }

    .linda-remember-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #CBD5E1;
        cursor: pointer;
        user-select: none;
    }

    .linda-checkbox {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        border: 1px solid #475569;
        background: rgba(8, 16, 28, 0.9);
        accent-color: #2563EB;
        cursor: pointer;
    }

    .linda-forgot-btn {
        background: none;
        border: none;
        font-size: 12px;
        font-weight: 600;
        color: #38BDF8;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .linda-forgot-btn:hover {
        color: #67E8F9;
        text-decoration: underline;
    }

    /* Primary Submit Button: Linda Vibrant Gradient */
    .linda-submit-btn {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        padding: 13.5px 20px !important;
        border-radius: 12px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #FFFFFF !important;
        background: linear-gradient(90deg, #10B981 0%, #06B6D4 50%, #2563EB 100%) !important;
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 10px 25px -4px rgba(6, 182, 212, 0.35) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .linda-submit-btn:hover {
        background: linear-gradient(90deg, #059669 0%, #0891B2 50%, #1D4ED8 100%) !important;
        box-shadow: 0 12px 30px -4px rgba(6, 182, 212, 0.5) !important;
        transform: translateY(-1px);
    }

    .linda-submit-btn:active {
        transform: translateY(0);
    }

    .linda-spinner {
        width: 18px;
        height: 18px;
        border: 2px solid #FFFFFF;
        border-top-color: transparent;
        border-radius: 9999px;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Separator */
    .linda-separator {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1.4rem 0 1.1rem;
    }

    .linda-sep-line {
        flex-grow: 1;
        height: 1px;
        background: rgba(51, 65, 85, 0.7);
    }

    .linda-sep-text {
        padding: 0 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94A3B8;
        background: transparent;
    }

    /* SSO Button */
    .linda-sso-btn {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        padding: 12px 16px !important;
        background: rgba(15, 27, 48, 0.7) !important;
        border: 1px solid rgba(71, 85, 105, 0.6) !important;
        border-radius: 12px !important;
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: #E2E8F0 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
    }

    .linda-sso-btn:hover {
        background: rgba(22, 38, 66, 0.9) !important;
        border-color: rgba(56, 189, 248, 0.4) !important;
        color: #FFFFFF !important;
    }

    /* Card Footer */
    .linda-card-footer {
        margin-top: 1.4rem;
        padding-top: 1.1rem;
        border-top: 1px solid rgba(51, 65, 85, 0.5);
        text-align: center;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .linda-footer-badge {
        font-size: 11px;
        color: #94A3B8;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-weight: 500;
        margin: 0;
    }

    .linda-footer-copyright {
        font-size: 10px;
        font-weight: 600;
        color: #64748B;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin: 0;
    }

    /* Modal Styling */
    .linda-modal-overlay {
        display: none !important;  /* Hidden by default, opened via JS */
        position: fixed;
        inset: 0;
        z-index: 50;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(4, 10, 20, 0.75);
        backdrop-filter: blur(8px);
    }

    .linda-modal-overlay.linda-modal-visible {
        display: flex !important; /* Shown when JS adds this class */
    }

    .linda-modal-box {
        background: #0D1B2E;
        border: 1px solid rgba(56, 189, 248, 0.3);
        border-radius: 20px;
        max-width: 380px;
        width: 100%;
        padding: 24px;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .linda-modal-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 9999px;
        background: rgba(6, 182, 212, 0.15);
        border: 1px solid rgba(6, 182, 212, 0.4);
        color: #38BDF8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .linda-modal-close-btn {
        width: 100%;
        padding: 11px 16px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        color: #FFFFFF;
        background: #2563EB;
        border: none;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .linda-modal-close-btn:hover {
        background: #1D4ED8;
    }

    /* Prevent browser autofill white background override */
    input:-webkit-autofill,
    input:-webkit-autofill:hover, 
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0px 1000px #091322 inset !important;
        -webkit-text-fill-color: #FFFFFF !important;
        border-color: #06B6D4 !important;
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
        button.style.opacity = '0.8';
        button.style.cursor = 'not-allowed';
        text.textContent = 'Memproses Masuk...';
        if (icon) icon.style.display = 'none';
        if (spinner) spinner.classList.remove('hidden');
    }

    function showForgotPasswordModal() {
        document.getElementById('forgot-modal').classList.add('linda-modal-visible');
    }

    function closeForgotPasswordModal() {
        document.getElementById('forgot-modal').classList.remove('linda-modal-visible');
    }
</script>
@endsection
