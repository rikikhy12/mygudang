<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Toko Daging | MYGUDANG MEAT ERP</title>
    
    <!-- Brand Favicon & Logo -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 128 128'%3E%3Cdefs%3E%3ClinearGradient id='meatGrad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%23E11D48'/%3E%3Cstop offset='50%25' stop-color='%23BE123C'/%3E%3Cstop offset='100%25' stop-color='%23881337'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='128' height='128' rx='28' fill='%230F172A'/%3E%3Ccircle cx='64' cy='64' r='46' fill='none' stroke='url(%23meatGrad)' stroke-width='6' stroke-dasharray='6 4'/%3E%3Cpath d='M44 76C40 68 40 56 48 46C56 36 68 36 78 42C88 48 92 60 88 70C84 80 72 86 60 84C52 82 46 80 44 76Z' fill='url(%23meatGrad)'/%3E%3Ccircle cx='62' cy='58' r='10' fill='%23FFFFFF' opacity='0.85'/%3E%3Ccircle cx='62' cy='58' r='6' fill='%23E2E8F0'/%3E%3Cpath d='M74 46C70 42 62 42 58 45' stroke='%23FFE4E6' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E">
    
    <!-- GT Walsheim Pro Font Stylesheet -->
    <link href="{{ asset('assets/fonts/stylesheet.css') }}" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Metronic Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-main: 'GT Walsheim Pro', 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --meat-crimson: #E11D48;
            --meat-crimson-dark: #BE123C;
            --meat-deep-red: #881337;
            --meat-amber: #F59E0B;
            --cold-cyan: #06B6D4;
            --obsidian-dark: #090B0E;
            --slate-panel: #11151F;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: var(--font-main) !important;
            background-color: #0B0E14;
            color: #F8FAFC;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            width: 100vw;
        }

        /* ========================================================
           LEFT HERO PANEL - BUTCHERY & COLD STORAGE THEME
        ======================================================== */
        .hero-panel {
            flex: 0 0 48%;
            background: 
                linear-gradient(145deg, rgba(9, 11, 14, 0.94) 0%, rgba(136, 19, 55, 0.88) 55%, rgba(9, 11, 14, 0.97) 100%),
                url('https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1500&q=80') center/cover no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4rem 4rem 3rem 4rem;
            color: #ffffff;
            overflow: hidden;
            box-shadow: inset -15px 0 35px rgba(0, 0, 0, 0.45);
        }

        .hero-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 15% 20%, rgba(225, 29, 72, 0.35) 0%, transparent 55%),
                        radial-gradient(circle at 85% 85%, rgba(6, 182, 212, 0.22) 0%, transparent 50%);
            pointer-events: none;
        }

        .hero-main-content {
            margin: auto 0;
            position: relative;
            z-index: 2;
        }

        .badge-meat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(225, 29, 72, 0.22);
            border: 1px solid rgba(244, 63, 94, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #FECDD3;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 16px rgba(225, 29, 72, 0.25);
        }

        .hero-title {
            font-size: 2.65rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.025em;
            margin-bottom: 0.85rem;
            color: #ffffff !important;
            text-shadow: 0 3px 18px rgba(0, 0, 0, 0.4);
        }

        .hero-title span {
            background: linear-gradient(135deg, #FDA4AF 0%, #FB7185 50%, #F43F5E 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.02rem;
            font-weight: 400;
            color: #E2E8F0 !important;
            line-height: 1.6;
            margin-bottom: 2.25rem;
            max-width: 520px;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        /* Feature Cards (Frosted Glass with Butchery Glow) */
        .glass-features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.85rem;
            margin-bottom: 2rem;
        }

        .glass-feature-card {
            background: rgba(17, 21, 31, 0.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 0.95rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.1rem;
            transition: all 0.28s ease;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .glass-feature-card:hover {
            background: rgba(225, 29, 72, 0.22);
            border-color: rgba(244, 63, 94, 0.45);
            transform: translateX(6px);
            box-shadow: 0 12px 28px rgba(225, 29, 72, 0.25);
        }

        .feature-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(225, 29, 72, 0.35) 0%, rgba(136, 19, 55, 0.5) 100%);
            border: 1px solid rgba(244, 63, 94, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #FECDD3;
            font-size: 1.25rem;
        }

        .feature-info {
            display: flex;
            flex-direction: column;
        }

        .feature-title {
            font-size: 0.94rem;
            font-weight: 700;
            color: #FFFFFF;
            letter-spacing: -0.01em;
            margin-bottom: 0.15rem;
        }

        .feature-desc {
            font-size: 0.81rem;
            color: #CBD5E1;
            line-height: 1.35;
        }

        /* Live Status Badge Pills */
        .status-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .status-indicator-chip {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 0.4rem 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: #E2E8F0;
        }

        .status-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 10px #10B981;
            animation: pulseGreen 2s infinite;
        }

        @keyframes pulseGreen {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(1.3); }
        }

        /* Live Clock Footer */
        .live-clock-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border-radius: 14px;
            padding: 0.85rem 1.4rem;
            position: relative;
            z-index: 2;
        }

        .live-clock-time {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: #FFFFFF !important;
            font-feature-settings: "tnum";
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
        }

        .live-clock-date {
            font-size: 0.84rem;
            font-weight: 500;
            color: #94A3B8 !important;
        }

        /* ========================================================
           RIGHT FORM PANEL - CRISP & ELEGANT
        ======================================================== */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0E121A;
            background-image: 
                radial-gradient(at 90% 10%, rgba(225, 29, 72, 0.08) 0px, transparent 50%),
                radial-gradient(at 10% 90%, rgba(6, 182, 212, 0.06) 0px, transparent 50%);
            padding: 3rem 2.5rem;
            position: relative;
        }

        .login-card-elevated {
            width: 100%;
            max-width: 450px;
            background: #141A24;
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 26px;
            padding: 3.25rem 2.65rem 2.5rem 2.65rem;
            box-shadow: 0 30px 70px -15px rgba(0, 0, 0, 0.6), 0 0 1px 1px rgba(255, 255, 255, 0.05);
            position: relative;
            margin: auto;
        }

        .floating-meat-badge {
            width: 82px;
            height: 82px;
            border-radius: 24px;
            background: linear-gradient(145deg, #1E2533 0%, #111622 100%);
            border: 2px solid rgba(225, 29, 72, 0.45);
            box-shadow: 0 12px 30px rgba(225, 29, 72, 0.3), inset 0 0 16px rgba(225, 29, 72, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -5.7rem auto 1.5rem auto;
            position: relative;
            z-index: 10;
        }

        .floating-meat-badge i {
            font-size: 2.2rem;
            color: #FB7185;
            filter: drop-shadow(0 2px 8px rgba(225, 29, 72, 0.4));
        }

        .form-header-title {
            font-size: 1.68rem;
            font-weight: 800;
            color: #FFFFFF;
            text-align: center;
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
        }

        .form-header-subtitle {
            font-size: 0.88rem;
            color: #94A3B8;
            text-align: center;
            margin-bottom: 2.1rem;
            line-height: 1.45;
        }

        .custom-input-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #E2E8F0;
            margin-bottom: 0.45rem;
            display: block;
        }

        .custom-input-group {
            position: relative;
            margin-bottom: 1.35rem;
        }

        .custom-input-group .input-icon-left {
            position: absolute;
            left: 1.15rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            font-size: 1.15rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .custom-input-group .input-icon-right {
            position: absolute;
            right: 1.15rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            font-size: 1.2rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .custom-input-group .input-icon-right:hover {
            color: #F43F5E;
        }

        .custom-input-field {
            width: 100%;
            height: 52px;
            padding: 0.65rem 1rem 0.65rem 3.1rem;
            background: #1C2433;
            border: 1.5px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            font-size: 0.94rem;
            color: #F8FAFC;
            font-weight: 500;
            font-family: var(--font-main);
            transition: all 0.25s ease;
        }

        .custom-input-field:focus {
            outline: none;
            border-color: #E11D48;
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.18);
            background: #212B3D;
        }

        .custom-input-field::placeholder {
            color: #64748B;
            font-weight: 400;
        }

        .btn-submit-meat {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #E11D48 0%, #BE123C 50%, #9F1239 100%);
            border: none;
            border-radius: 14px;
            color: #FFFFFF;
            font-size: 0.98rem;
            font-weight: 700;
            font-family: var(--font-main);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            box-shadow: 0 12px 28px -5px rgba(225, 29, 72, 0.5);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-top: 1rem;
        }

        .btn-submit-meat:hover {
            background: linear-gradient(135deg, #F43F5E 0%, #E11D48 50%, #BE123C 100%);
            box-shadow: 0 16px 32px -5px rgba(225, 29, 72, 0.65);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        .btn-submit-meat:active {
            transform: translateY(0);
        }

        .demo-accounts-wrapper {
            margin-top: 1.85rem;
            padding-top: 1.35rem;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .demo-chip {
            font-size: 0.76rem;
            font-weight: 600;
            font-family: var(--font-main);
            padding: 0.45rem 0.75rem;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            color: #CBD5E1;
            cursor: pointer;
            transition: all 0.2s;
        }

        .demo-chip:hover {
            background: rgba(225, 29, 72, 0.18);
            color: #FECDD3;
            border-color: rgba(225, 29, 72, 0.4);
            transform: translateY(-1px);
        }

        .footer-system-note {
            text-align: center;
            font-size: 0.74rem;
            color: #64748B;
            margin-top: 1.6rem;
        }

        @media (max-width: 991.98px) {
            .login-wrapper {
                flex-direction: column;
            }
            .hero-panel {
                flex: none;
                padding: 3rem 2rem;
            }
            .form-panel {
                padding: 4rem 1.25rem 2.5rem 1.25rem;
            }
            .login-card-elevated {
                padding: 2.6rem 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- ========================================================
             LEFT HERO PANEL - BUTCHERY & COLD STORAGE SHOWCASE
        ======================================================== -->
        <div class="hero-panel">
            <div class="hero-main-content">
                <div class="badge-meat-pill">
                    <i class="bi bi-fire text-danger"></i>
                    <span>Sistem Industri Toko Daging & Cold Storage</span>
                </div>

                <h1 class="hero-title">
                    MYGUDANG <span>MEAT ERP</span>
                </h1>
                <p class="hero-subtitle">
                    Solusi terpadu pengadaan karkas, butchery deboning, dual-unit timbangan Koli & Kg (Catch Weight), gudang dingin chiller & kasir toko daging.
                </p>

                <!-- 4 Frosted Glass Butchery Features -->
                <div class="glass-features-grid">
                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Dual-Unit Stock (Catch Weight Engine)</span>
                            <span class="feature-desc">Presisi timbangan kilogram (Kg) riil & kemasan fisik (Koli/Bal) di setiap mutasi stok.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-snow2"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Cold Storage & Lot Traceability</span>
                            <span class="feature-desc">Kontrol suhu pendingin, nomor batch karkas, tanggal potong & mutasi multi-gudang.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-scissors"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Butchery, Deboning & Yield Formula</span>
                            <span class="feature-desc">Konversi karkas ke potongan primal (Sirloin, Tenderloin, Ribeye) & kalkulasi HPP otomatis.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Bypass Direct GRN & Kasir POS Daging</span>
                            <span class="feature-desc">Penerimaan barang cepat tanpa PO, integrasi kasir retail & pembukuan jurnal akuntansi.</span>
                        </div>
                    </div>
                </div>

                <!-- Status Strip -->
                <div class="status-strip">
                    <div class="status-indicator-chip">
                        <span class="status-dot-pulse"></span>
                        <span>Chiller Room: -2°C ~ 2°C (Optimal)</span>
                    </div>
                    <div class="status-indicator-chip">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Dual-Unit Balance: Active</span>
                    </div>
                    <div class="status-indicator-chip">
                        <i class="bi bi-database-check text-info"></i>
                        <span>Auto-Journal GL: Synced</span>
                    </div>
                </div>
            </div>

            <!-- Real-Time Live Clock -->
            <div class="live-clock-box">
                <div>
                    <div class="live-clock-time" id="liveClockTime">00:00:00 WIB</div>
                    <div class="live-clock-date" id="liveClockDate">Memuat tanggal...</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger bg-opacity-25 text-danger px-3 py-2 rounded-pill fw-bold fs-8">
                        <i class="bi bi-dot"></i> LIVE CLOUD
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             RIGHT FORM PANEL - LOGIN CARD
        ======================================================== -->
        <div class="form-panel">
            <div class="login-card-elevated">
                <!-- Floating Butchery Logo Emblem -->
                <div class="floating-meat-badge">
                    <i class="bi bi-shop"></i>
                </div>

                <h2 class="form-header-title">Masuk ke Sistem</h2>
                <p class="form-header-subtitle">Silakan autentikasi akun Toko Daging & ERP Anda</p>

                @if($errors->any())
                    <div class="alert alert-dismissible bg-danger bg-opacity-10 d-flex align-items-center p-3 mb-4 rounded-3 border border-danger border-opacity-25">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-2"></i>
                        <div class="text-danger fw-semibold fs-8">{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="custom-input-label" for="emailInput">Alamat Email Pengguna</label>
                        <div class="custom-input-group">
                            <i class="bi bi-envelope input-icon-left"></i>
                            <input type="email" name="email" id="emailInput" class="custom-input-field" value="{{ old('email', 'admin@erp.local') }}" required autofocus placeholder="admin@erp.local" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="custom-input-label" for="passwordInput">Kata Sandi (Password)</label>
                        <div class="custom-input-group">
                            <i class="bi bi-lock input-icon-left"></i>
                            <input type="password" name="password" id="passwordInput" class="custom-input-field" value="password" required placeholder="Masukkan kata sandi..." />
                            <button type="button" class="input-icon-right" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Kata Sandi">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input bg-dark border-secondary" type="checkbox" name="remember" id="rememberMe" checked>
                            <label class="form-check-label fs-8 text-muted fw-semibold" for="rememberMe">
                                Ingat sesi login saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-meat">
                        <span>Masuk ke Dashboard Daging</span>
                        <i class="bi bi-arrow-right fs-5"></i>
                    </button>
                </form>

                <div class="demo-accounts-wrapper">
                    <div class="fs-9 text-muted fw-bold text-uppercase mb-2 letter-spacing-1">Akses Akun Cepat (Quick Demo)</div>
                    <div class="d-flex flex-wrap gap-1.5 justify-content-center">
                        <button type="button" class="demo-chip" onclick="fillLogin('admin@erp.local')">
                            <i class="bi bi-shield-lock text-danger me-1"></i> Admin Utama
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('purchasing@erp.local')">
                            <i class="bi bi-box-arrow-in-down text-warning me-1"></i> Butchery & Gudang
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('sales@erp.local')">
                            <i class="bi bi-bag-check text-info me-1"></i> Kasir Toko
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('finance@erp.local')">
                            <i class="bi bi-wallet2 text-success me-1"></i> Keuangan (AP/AR)
                        </button>
                    </div>
                </div>

                <div class="footer-system-note">
                    MYGUDANG MEAT ERP &bull; Dual-Unit Catch Weight v2.4
                </div>
            </div>
        </div>
    </div>

    <!-- Live Clock & Password Toggle Script -->
    <script>
        function updateLiveClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('liveClockTime').textContent = `${hours}:${minutes}:${seconds} WIB`;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayName = days[now.getDay()];
            const date = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            document.getElementById('liveClockDate').textContent = `${dayName}, ${date} ${monthName} ${year}`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        function togglePasswordVisibility() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        function fillLogin(email) {
            document.getElementById('emailInput').value = email;
            document.getElementById('passwordInput').value = 'password';
        }
    </script>
</body>
</html>
