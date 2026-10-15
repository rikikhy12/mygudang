<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login Penjualan Daging & POS | MYGUDANG MEAT</title>
    
    <!-- Brand Favicon & Logo -->
    <link rel="icon" type="image/png" href="{{ asset('assets/media/logos/Logosg.png') }}">
    
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
            --meat-crimson-light: #FFF1F2;
            --meat-crimson-border: #FECDD3;
            --text-heading: #0F172A;
            --text-body: #1E293B;
            --text-muted: #475569;
            --border-card: #E2E8F0;
            --bg-page: #F8FAFC;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: var(--font-main) !important;
            background-color: var(--bg-page);
            color: var(--text-body);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            width: 100vw;
            background: #FFFFFF;
        }

        /* ========================================================
           LEFT HERO PANEL - BUTCHERY & POS SHOWCASE
           High Opacity White Backdrop for 100% Readability
        ======================================================== */
        .hero-panel {
            flex: 0 0 50%;
            background: 
                linear-gradient(135deg, rgba(255, 255, 255, 0.94) 0%, rgba(255, 248, 250, 0.92) 50%, rgba(254, 238, 242, 0.95) 100%),
                url('https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1500&q=80') center/cover no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem 3.5rem 2.5rem 3.5rem;
            color: var(--text-heading);
            overflow: hidden;
            border-right: 1px solid #E2E8F0;
        }

        .hero-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 10% 15%, rgba(225, 29, 72, 0.08) 0%, transparent 55%),
                radial-gradient(circle at 90% 85%, rgba(244, 63, 94, 0.05) 0%, transparent 50%);
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
            background: #FFF1F2;
            border: 1.5px solid #FECDD3;
            padding: 0.45rem 1.15rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #BE123C;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 10px rgba(225, 29, 72, 0.08);
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.16;
            letter-spacing: -0.025em;
            margin-bottom: 0.75rem;
            color: var(--text-heading) !important;
        }

        .hero-title span {
            color: var(--meat-crimson);
            position: relative;
        }

        .hero-subtitle {
            font-size: 0.98rem;
            font-weight: 500;
            color: var(--text-muted) !important;
            line-height: 1.55;
            margin-bottom: 1.75rem;
            max-width: 540px;
        }

        /* 4 Clean Solid White Feature Cards */
        .glass-features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .glass-feature-card {
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            padding: 0.9rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1.1rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
        }

        .glass-feature-card:hover {
            border-color: #FECDD3;
            transform: translateX(5px);
            box-shadow: 0 8px 20px rgba(225, 29, 72, 0.08);
        }

        .feature-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 13px;
            background: #FFF1F2;
            border: 1.5px solid #FECDD3;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--meat-crimson);
            font-size: 1.25rem;
        }

        .feature-info {
            display: flex;
            flex-direction: column;
        }

        .feature-title {
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--text-heading);
            letter-spacing: -0.01em;
            margin-bottom: 0.15rem;
        }

        .feature-desc {
            font-size: 0.81rem;
            color: var(--text-muted);
            line-height: 1.35;
            font-weight: 450;
        }

        /* Status Strip */
        .status-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 0.5rem;
        }

        .status-indicator-chip {
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 10px;
            padding: 0.4rem 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-body);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .status-dot-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);
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
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            padding: 0.8rem 1.35rem;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
            margin-top: 1rem;
        }

        .live-clock-time {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: var(--text-heading) !important;
            font-feature-settings: "tnum";
        }

        .live-clock-date {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted) !important;
        }

        /* ========================================================
           RIGHT FORM PANEL - CRISP & DISTINCT WHITE CARD
        ======================================================== */
        .form-panel {
            flex: 0 0 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F4F6F9;
            background-image: 
                radial-gradient(at 100% 0%, rgba(225, 29, 72, 0.04) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(59, 130, 246, 0.03) 0px, transparent 50%);
            padding: 3rem 2.5rem;
            position: relative;
        }

        .login-card-elevated {
            width: 100%;
            max-width: 440px;
            background: #FFFFFF;
            border: 1.5px solid #E2E8F0;
            border-radius: 26px;
            padding: 3.25rem 2.5rem 2.5rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(226, 232, 240, 0.8);
            position: relative;
            margin: auto;
        }

        /* Floating Logo Badge with Original Rizal Store Logo */
        .floating-logo-badge {
            width: 88px;
            height: 88px;
            border-radius: 24px;
            background: #FFFFFF;
            border: 2px solid #E2E8F0;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -5.9rem auto 1.5rem auto;
            position: relative;
            z-index: 10;
            padding: 8px;
        }

        .floating-logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .form-header-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--text-heading);
            text-align: center;
            margin-bottom: 0.3rem;
            letter-spacing: -0.02em;
        }

        .form-header-subtitle {
            font-size: 0.88rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.45;
            font-weight: 450;
        }

        .custom-input-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-heading);
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
            color: var(--meat-crimson);
        }

        .custom-input-field {
            width: 100%;
            height: 52px;
            padding: 0.65rem 1rem 0.65rem 3.1rem;
            background: #FFFFFF;
            border: 1.5px solid #CBD5E1;
            border-radius: 14px;
            font-size: 0.94rem;
            color: var(--text-heading);
            font-weight: 500;
            font-family: var(--font-main);
            transition: all 0.25s ease;
        }

        .custom-input-field:focus {
            outline: none;
            border-color: var(--meat-crimson);
            box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
            background: #FFFFFF;
        }

        .custom-input-field::placeholder {
            color: #94A3B8;
            font-weight: 400;
        }

        .btn-submit-meat {
            width: 100%;
            height: 52px;
            background: linear-gradient(135deg, #E11D48 0%, #BE123C 100%);
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
            box-shadow: 0 10px 24px -5px rgba(225, 29, 72, 0.45);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-top: 1rem;
        }

        .btn-submit-meat:hover {
            background: linear-gradient(135deg, #F43F5E 0%, #E11D48 100%);
            box-shadow: 0 14px 28px -5px rgba(225, 29, 72, 0.6);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        .btn-submit-meat:active {
            transform: translateY(0);
        }

        .demo-accounts-wrapper {
            margin-top: 1.85rem;
            padding-top: 1.35rem;
            border-top: 1px dashed #CBD5E1;
            text-align: center;
        }

        .demo-chip {
            font-size: 0.76rem;
            font-weight: 600;
            font-family: var(--font-main);
            padding: 0.45rem 0.75rem;
            border-radius: 10px;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
            color: var(--text-body);
            cursor: pointer;
            transition: all 0.2s;
        }

        .demo-chip:hover {
            background: #FFF1F2;
            color: var(--meat-crimson);
            border-color: #FECDD3;
            transform: translateY(-1px);
        }

        .footer-system-note {
            text-align: center;
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 1.6rem;
            font-weight: 500;
        }

        @media (max-width: 991.98px) {
            .login-wrapper {
                flex-direction: column;
            }
            .hero-panel {
                flex: none;
                padding: 3rem 2rem;
                border-right: none;
                border-bottom: 1px solid #E2E8F0;
            }
            .form-panel {
                flex: none;
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
             LEFT HERO PANEL - BUTCHERY & POS SHOWCASE
             High-Opacity White Layer for 100% Crisp Readability
        ======================================================== -->
        <div class="hero-panel">
            <div class="hero-main-content">
                <div class="badge-meat-pill">
                    <i class="bi bi-cart-check-fill text-danger"></i>
                    <span>Sistem Penjualan Daging & Kasir POS Terpadu</span>
                </div>

                <h1 class="hero-title">
                    MYGUDANG <span>MEAT & POS</span>
                </h1>
                <p class="hero-subtitle">
                    Platform terpadu kasir toko daging (Point of Sale), timbangan berat riil Kg & Koli kemasan (Catch Weight), penerimaan barang direct tanpa PO, serta kontrol stok karkas chiller.
                </p>

                <!-- 4 Crisp White Feature Cards with Meat & POS Highlights -->
                <div class="glass-features-grid">
                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Kasir POS Toko Daging (Point of Sale)</span>
                            <span class="feature-desc">Transaksi kasir retail & grosir cepat, scan barcode potongan, timbangan Kg digital, multi-pembayaran & struk nota thermal.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Dual-Unit (Catch Weight Koli & Kg)</span>
                            <span class="feature-desc">Presisi timbangan kilogram (Kg) riil & kuantitas kemasan (Koli/Bal/Dus) di setiap penjualan, mutasi stok, dan retur.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Bypass Penerimaan Barang (Direct GRN Tanpa PO)</span>
                            <span class="feature-desc">Terima pasokan karkas/daging langsung dari supplier atau RPH tanpa perlu PO, stok otomatis bertambah & terjurnal.</span>
                        </div>
                    </div>

                    <div class="glass-feature-card">
                        <div class="feature-icon-box">
                            <i class="bi bi-snow2"></i>
                        </div>
                        <div class="feature-info">
                            <span class="feature-title">Butchery, Yield Karkas & Chiller Storage</span>
                            <span class="feature-desc">Formula deboning karkas ke potongan primal (Sirloin, Tenderloin, Ribeye, Wagyu, Tetelan) & pelacakan lot cold storage.</span>
                        </div>
                    </div>
                </div>

                <!-- Status Strip -->
                <div class="status-strip">
                    <div class="status-indicator-chip">
                        <span class="status-dot-pulse"></span>
                        <span>Kasir POS: Ready</span>
                    </div>
                    <div class="status-indicator-chip">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>Dual-Unit (Koli + Kg): Active</span>
                    </div>
                    <div class="status-indicator-chip">
                        <i class="bi bi-thermometer-half text-info"></i>
                        <span>Chiller Room: -2°C ~ 2°C Optimal</span>
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
                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-bold fs-8 border border-danger border-opacity-25">
                        <i class="bi bi-dot"></i> SISTEM AKTIF
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================================
             RIGHT FORM PANEL - PURE WHITE CARD & HIGH CONTRAST
        ======================================================== -->
        <div class="form-panel">
            <div class="login-card-elevated">
                <!-- Floating Logo Badge with Original Logo Image -->
                <div class="floating-logo-badge">
                    <img src="{{ asset('assets/media/logos/Logosg.png') }}" alt="Logo Rizal Store">
                </div>

                <h2 class="form-header-title">Masuk ke Sistem</h2>
                <p class="form-header-subtitle">Silakan autentikasi akun Penjualan Daging & POS Anda</p>

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
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                            <label class="form-check-label fs-8 text-muted fw-semibold" for="rememberMe">
                                Ingat sesi login saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-meat">
                        <span>Masuk ke Kasir & Sistem Daging</span>
                        <i class="bi bi-arrow-right fs-5"></i>
                    </button>
                </form>

                <div class="demo-accounts-wrapper">
                    <div class="fs-9 text-muted fw-bold text-uppercase mb-2 letter-spacing-1">Akses Akun Cepat (Quick Demo)</div>
                    <div class="d-flex flex-wrap gap-1.5 justify-content-center">
                        <button type="button" class="demo-chip" onclick="fillLogin('sales@erp.local')">
                            <i class="bi bi-shop text-danger me-1"></i> Kasir POS Toko
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('admin@erp.local')">
                            <i class="bi bi-shield-lock text-primary me-1"></i> Admin Utama
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('purchasing@erp.local')">
                            <i class="bi bi-box-arrow-in-down text-warning me-1"></i> Butchery & Gudang
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('finance@erp.local')">
                            <i class="bi bi-wallet2 text-success me-1"></i> Keuangan (AP/AR)
                        </button>
                    </div>
                </div>

                <div class="footer-system-note">
                    MYGUDANG MEAT & POS ERP &bull; Dual-Unit Catch Weight v2.4
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
