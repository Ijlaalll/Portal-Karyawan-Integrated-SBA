@extends('layouts.app')

@section('title', 'Masuk - GA One | Portal Karyawan Terintegrasi SBA')

@section('content')
<!-- Container Utama Halaman Login -->
<div class="linda-login-wrapper">

    <!-- 1. Foto Pabrik SBA (tampak jelas sebagai background, seperti Linda menampilkan foto tambang) -->
    <div class="gaone-bg"></div>

    <!-- 2. Overlay Directional: kiri lebih gelap untuk readability, kanan lebih transparan agar foto terlihat -->
    <div class="gaone-overlay"></div>

    <!-- 3. Ambient Glow Biru GA One -->
    <div class="gaone-glows"></div>

    <!-- 4. Ambient Network SVG — subtle decoration di sudut kiri bawah, tidak mengganggu konten -->
    <div class="gaone-network-bg">
        <svg viewBox="0 0 700 500" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;">
            <!-- Garis jaringan biru tipis sebagai dekorasi ambient -->
            <line x1="20" y1="480" x2="120" y2="380" stroke="rgba(37,99,235,0.4)" stroke-width="1.2"/>
            <line x1="120" y1="380" x2="80" y2="260" stroke="rgba(56,189,248,0.35)" stroke-width="1.2"/>
            <line x1="80" y1="260" x2="30" y2="180" stroke="rgba(37,99,235,0.28)" stroke-width="1"/>
            <line x1="120" y1="380" x2="240" y2="420" stroke="rgba(56,189,248,0.32)" stroke-width="1.2"/>
            <line x1="240" y1="420" x2="350" y2="360" stroke="rgba(37,99,235,0.25)" stroke-width="1" stroke-dasharray="5 4"/>
            <line x1="80" y1="260" x2="200" y2="290" stroke="rgba(147,197,253,0.22)" stroke-width="1" stroke-dasharray="4 5"/>
            <line x1="200" y1="290" x2="350" y2="360" stroke="rgba(37,99,235,0.2)" stroke-width="1" stroke-dasharray="5 4"/>
            <line x1="350" y1="360" x2="460" y2="400" stroke="rgba(56,189,248,0.22)" stroke-width="1" stroke-dasharray="4 5"/>
            <!-- Titik simpul bersinar -->
            <circle cx="20" cy="480" r="3.5" fill="#2563EB" opacity="0.8"/>
            <circle cx="120" cy="380" r="5" fill="#38BDF8" opacity="0.9"/>
            <circle cx="80" cy="260" r="4" fill="#60A5FA" opacity="0.85"/>
            <circle cx="30" cy="180" r="3" fill="#2563EB" opacity="0.7"/>
            <circle cx="240" cy="420" r="4.5" fill="#38BDF8" opacity="0.8"/>
            <circle cx="200" cy="290" r="3.5" fill="#93C5FD" opacity="0.7"/>
            <circle cx="350" cy="360" r="5" fill="#2563EB" opacity="0.85"/>
            <circle cx="460" cy="400" r="3" fill="#60A5FA" opacity="0.65"/>
        </svg>
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

            <!-- 6 Modul Utama Layanan SBA — ikon konsisten biru GA One -->
            <div class="linda-modules-grid">
                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-blue">meeting_room</span>
                    <div>
                        <div class="linda-mod-title">Ruang Meeting</div>
                        <div class="linda-mod-desc">Offline &amp; Hybrid</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-sky">inventory_2</span>
                    <div>
                        <div class="linda-mod-title">Logistik ATK</div>
                        <div class="linda-mod-desc">Katalog &amp; Stok</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-light">badge</span>
                    <div>
                        <div class="linda-mod-title">Izin Keluar (SIK)</div>
                        <div class="linda-mod-desc">QR Code Gate</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-sky">directions_car</span>
                    <div>
                        <div class="linda-mod-title">Kendaraan</div>
                        <div class="linda-mod-desc">Armada &amp; Supir</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-blue">wc</span>
                    <div>
                        <div class="linda-mod-title">Inspeksi Toilet</div>
                        <div class="linda-mod-desc">Checklist Pabrik</div>
                    </div>
                </div>

                <div class="linda-mod-pill">
                    <span class="material-symbols-outlined gaone-icon-light">verified_user</span>
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
                        Integrated <span style="color:#60A5FA;font-weight:700;">Monitoring</span> &amp; <span style="color:#93C5FD;font-weight:700;">Service</span> System App
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

    /* 2. Full Viewport Aerial Factory Background — foto pabrik SBA tampak jelas seperti Linda menampilkan foto tambang */
    .gaone-bg {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 0 !important;
        background-image: url('{{ asset('images/sba-factory-aerial.jpg') }}?v=4') !important;
        background-size: cover !important;
        background-position: center 35% !important;
        background-repeat: no-repeat !important;
        pointer-events: none;
    }

    /* 3. Overlay — tipis di kanan agar foto pabrik terlihat, lebih gelap di kiri untuk readability teks hero */
    .gaone-overlay {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 1 !important;
        /* Gradient: kiri gelap (agar teks Hero terbaca) -> tengah semi-transparan -> kanan agak gelap (agar kartu kontras) */
        background: linear-gradient(
            to right,
            rgba(5, 14, 35, 0.82) 0%,
            rgba(5, 14, 35, 0.55) 30%,
            rgba(5, 14, 35, 0.32) 58%,
            rgba(5, 14, 35, 0.52) 100%
        ) !important;
        pointer-events: none;
    }

    /* 4. Ambient Radial Glow Lights — identitas warna biru GA One */
    .gaone-glows {
        position: fixed;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background:
            radial-gradient(ellipse at 10% 55%, rgba(37, 99, 235, 0.28) 0%, transparent 45%),
            radial-gradient(ellipse at 85% 20%, rgba(6, 182, 212, 0.18) 0%, transparent 40%),
            radial-gradient(ellipse at 50% 95%, rgba(37, 99, 235, 0.14) 0%, transparent 50%);
    }


    /* 5. Ambient Network SVG Background (sudut kiri bawah, tidak overlap konten) */
    .gaone-network-bg {
        position: fixed;
        bottom: 0;
        left: 0;
        width: min(55vw, 700px);
        height: min(65vh, 500px);
        z-index: 3;
        pointer-events: none;
        opacity: 0.85;
    }

    /* Icon warna biru GA One untuk module pills */
    .gaone-icon-blue  { font-size: 18px; color: #2563EB; }
    .gaone-icon-sky   { font-size: 18px; color: #38BDF8; }
    .gaone-icon-light { font-size: 18px; color: #93C5FD; }

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

    /* Identitas GA One: gradasi biru royal -> biru muda -> putih cerah */
    .linda-gradient-text {
        background: linear-gradient(90deg, #60A5FA 0%, #93C5FD 50%, #FFFFFF 100%);
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

    .linda-tag-blue { color: #93C5FD; font-weight: 700; }
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
        /* Deep Navy Blue Glassmorphism — identitas biru GA One, efek frosted glass */
        background: rgba(10, 20, 48, 0.82) !important;
        backdrop-filter: blur(36px) saturate(1.6) brightness(0.95) !important;
        -webkit-backdrop-filter: blur(36px) saturate(1.6) brightness(0.95) !important;
        /* Border: kiri-atas lebih terang (highlight), kanan-bawah lebih gelap (shadow) */
        border: 1px solid rgba(56, 189, 248, 0.22) !important;
        border-radius: 26px !important;
        padding: 34px 34px 28px !important;
        /* Outer glow biru + drop shadow dalam */
        box-shadow:
            0 24px 64px -12px rgba(0, 0, 0, 0.75),
            0 0 0 1px rgba(37, 99, 235, 0.15),
            0 0 60px -15px rgba(37, 99, 235, 0.5),
            inset 0 1px 0 rgba(147, 197, 253, 0.12) !important;  /* inner top highlight */
        position: relative !important;
        overflow: hidden !important;
    }

    .linda-card-top-accent {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        /* Identitas GA One: gradasi biru cerah */
        background: linear-gradient(90deg, transparent 0%, #2563EB 30%, #38BDF8 65%, transparent 100%);
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
        color: #2563EB;
    }

    .linda-input {
        width: 100% !important;
        /* Input dark navy — lebih gelap dari kartu, kontras tinggi untuk teks */
        background: rgba(5, 12, 30, 0.88) !important;
        border: 1px solid rgba(56, 189, 248, 0.2) !important;
        border-radius: 12px !important;
        padding: 12px 16px 12px 42px !important;
        font-size: 13.5px !important;
        color: #F0F6FF !important;
        outline: none !important;
        transition: all 0.22s ease !important;
    }

    .linda-input::placeholder {
        color: #4B6080 !important;
    }

    .linda-input:focus {
        background: rgba(5, 12, 30, 0.98) !important;
        /* Focus ring biru GA One */
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.28) !important;
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

    /* Primary Submit Button: Identitas Biru GA One */
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
        /* GA One blue: biru tua -> biru cerah -> cyan */
        background: linear-gradient(90deg, #1D4ED8 0%, #2563EB 50%, #38BDF8 100%) !important;
        border: none !important;
        cursor: pointer !important;
        box-shadow: 0 8px 20px -4px rgba(37, 99, 235, 0.55) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .linda-submit-btn:hover {
        background: linear-gradient(90deg, #1E40AF 0%, #1D4ED8 50%, #2563EB 100%) !important;
        box-shadow: 0 10px 28px -4px rgba(37, 99, 235, 0.7) !important;
        transform: translateY(-1.5px);
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
