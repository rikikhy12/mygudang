<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login | MYGUDANG</title>
    <!-- Brand Favicon & Logo -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 128 128'%3E%3Cdefs%3E%3ClinearGradient id='bgGrad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230B132B'/%3E%3Cstop offset='100%25' stop-color='%23111C38'/%3E%3C/linearGradient%3E%3ClinearGradient id='blueGrad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%231D4ED8'/%3E%3Cstop offset='60%25' stop-color='%232563EB'/%3E%3Cstop offset='100%25' stop-color='%2338BDF8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='128' height='128' rx='28' fill='url(%23bgGrad)' stroke='%2338BDF8' stroke-width='4' stroke-opacity='0.4'/%3E%3Cg transform='translate(6, 6)'%3E%3Cpath d='M14 84V26C14 20 19 16 26 19L48 38L70 19C77 16 82 20 82 26V52' fill='none' stroke='url(%23blueGrad)' stroke-width='14' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M108 34a34 34 0 0 0-24-11 38 38 0 0 0-38 38v0a38 38 0 0 0 38 38 36 36 0 0 0 27-12' fill='none' stroke='%23FFFFFF' stroke-width='13' stroke-linecap='round'/%3E%3Cpath d='M78 50L68 60L78 70' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M83 72L92 48' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round'/%3E%3Cpath d='M97 50L107 60L97 70' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3Crect x='110' y='14' width='12' height='12' rx='3' fill='%2338BDF8'/%3E%3Crect x='124' y='14' width='12' height='12' rx='3' fill='%232563EB'/%3E%3Crect x='117' y='28' width='14' height='14' rx='3.5' fill='%2360A5FA'/%3E%3C/g%3E%3C/svg%3E">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Metronic Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            background-color: #ffffff;
            overflow-x: hidden;
        }
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            width: 100vw;
        }
        
        /* Left Hero Panel with Corporate Texture Overlay */
        .hero-panel {
            flex: 0 0 45%;
            background: linear-gradient(135deg, rgba(29, 78, 216, 0.94) 0%, rgba(37, 99, 235, 0.90) 50%, rgba(30, 64, 175, 0.96) 100%), 
                        url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1400&q=80') center/cover no-repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4.5rem 4rem 3.5rem 4rem;
            color: #ffffff;
            overflow: hidden;
            box-shadow: inset -10px 0 30px rgba(0, 0, 0, 0.1);
        }
        .hero-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 30%, rgba(56, 189, 248, 0.25) 0%, transparent 60%);
            pointer-events: none;
        }
        
        /* Centered Hero Content */
        .hero-main-content {
            margin: auto 0;
            position: relative;
            z-index: 2;
        }
        .hero-icon-pill {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.35);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }
        .hero-title {
            font-size: 2.35rem;
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -0.025em;
            margin-bottom: 0.85rem;
            color: #ffffff !important;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
        }
        .hero-subtitle {
            font-size: 1.02rem;
            font-weight: 400;
            color: #ffffff !important;
            opacity: 0.95;
            line-height: 1.55;
            margin-bottom: 2.5rem;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* 3 Frosted Glass Pills */
        .glass-pill {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.32);
            border-radius: 16px;
            padding: 1rem 1.35rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .glass-pill:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(5px);
            border-color: rgba(255, 255, 255, 0.45);
        }
        .glass-check-icon {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.32);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #ffffff !important;
        }
        .glass-pill-text {
            font-size: 0.94rem;
            font-weight: 600;
            color: #ffffff !important;
            letter-spacing: -0.01em;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
        }
        
        /* Live Clock Footer */
        .live-clock-box {
            text-align: center;
            color: #ffffff !important;
            padding-top: 2rem;
            position: relative;
            z-index: 2;
        }
        .live-clock-time {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            color: #ffffff !important;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .live-clock-date {
            font-size: 0.86rem;
            font-weight: 500;
            color: #ffffff !important;
            opacity: 0.85;
            margin-top: 0.2rem;
        }

        /* Right Form Panel */
        .form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 3rem 2rem;
            position: relative;
        }
        .login-card-elevated {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 28px;
            padding: 3rem 2.5rem 2.5rem 2.5rem;
            box-shadow: 0 25px 65px -15px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.04);
            position: relative;
            margin: auto;
        }
        .floating-logo-badge {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 14px 35px rgba(37, 99, 235, 0.18), 0 0 0 1px rgba(226, 232, 240, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -5.2rem auto 1.5rem auto;
            position: relative;
            z-index: 10;
        }
        .floating-logo-badge svg {
            width: 44px;
            height: 44px;
        }
        .form-header-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            text-align: center;
            margin-bottom: 0.35rem;
            letter-spacing: -0.015em;
        }
        .form-header-subtitle {
            font-size: 0.86rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.45;
        }
        .custom-input-label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.45rem;
            display: block;
        }
        .custom-input-group {
            position: relative;
            margin-bottom: 1.25rem;
        }
        .custom-input-group .input-icon-left {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 0.2s;
        }
        .custom-input-group .input-icon-right {
            position: absolute;
            right: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.15rem;
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
            color: #2563eb;
        }
        .custom-input-field {
            width: 100%;
            height: 50px;
            padding: 0.65rem 1rem 0.65rem 3rem;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.92rem;
            color: #0f172a;
            font-weight: 500;
            transition: all 0.25s ease;
        }
        .custom-input-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            background: #ffffff;
        }
        .custom-input-field::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .btn-submit-primary {
            width: 100%;
            height: 50px;
            background: #3b82f6;
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-size: 0.96rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 22px -5px rgba(59, 130, 246, 0.45);
            transition: all 0.25s ease;
            cursor: pointer;
            margin-top: 0.75rem;
        }
        .btn-submit-primary:hover {
            background: #2563eb;
            box-shadow: 0 12px 26px -5px rgba(37, 99, 235, 0.55);
            transform: translateY(-1px);
            color: #ffffff;
        }
        .btn-submit-primary:active {
            transform: translateY(0);
        }
        .demo-accounts-wrapper {
            margin-top: 1.75rem;
            padding-top: 1.25rem;
            border-top: 1px dashed #e2e8f0;
            text-align: center;
        }
        .demo-chip {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.7rem;
            border-radius: 9px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .demo-chip:hover {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
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
                padding: 3.5rem 1.25rem 2rem 1.25rem;
            }
            .login-card-elevated {
                padding: 2.5rem 1.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Left Hero Panel with Corporate Texture -->
        <div class="hero-panel">
            <div class="hero-main-content">
                <div class="hero-icon-pill">
                    <i class="bi bi-buildings-fill fs-3 text-white"></i>
                </div>
                <h1 class="hero-title">MYGUDANG</h1>
                <p class="hero-subtitle">Kelola operasional, penjualan, stok, absensi & keuangan dalam satu sistem terpadu.</p>

                <!-- 3 Frosted Glass Feature Cards -->
                <div class="glass-pill">
                    <div class="glass-check-icon">
                        <i class="bi bi-check2 text-white fs-6 fw-bold"></i>
                    </div>
                    <span class="glass-pill-text">Data karyawan, operasional & keuangan tersentralisasi & aman</span>
                </div>

                <div class="glass-pill">
                    <div class="glass-check-icon">
                        <i class="bi bi-check2 text-white fs-6 fw-bold"></i>
                    </div>
                    <span class="glass-pill-text">Otomasi stok multi-satuan UOM, faktur & jurnal akuntansi</span>
                </div>

                <div class="glass-pill">
                    <div class="glass-check-icon">
                        <i class="bi bi-check2 text-white fs-6 fw-bold"></i>
                    </div>
                    <span class="glass-pill-text">Laporan real-time untuk pengambilan keputusan bisnis</span>
                </div>
            </div>

            <!-- Real-Time Live Clock -->
            <div class="live-clock-box">
                <div class="live-clock-time" id="liveClockTime">07:09:48</div>
                <div class="live-clock-date" id="liveClockDate">Senin, 14 September 2026</div>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="form-panel">
            <div class="login-card-elevated">
                <!-- Floating Logo Badge -->
                <div class="floating-logo-badge shadow-sm" style="background: #ffffff; border: 1px solid #e2e8f0; padding: 6px;">
                    <img src="{{ asset('assets/media/logos/Logosg.png') }}" alt="Rizal Store Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>

                <h2 class="form-header-title">Selamat Datang</h2>
                <p class="form-header-subtitle">Silakan masuk ke akun MYGUDANG Anda</p>

                @if($errors->any())
                    <div class="alert alert-dismissible bg-light-danger d-flex align-items-center p-3 mb-4 rounded-3 border border-danger border-dashed">
                        <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-2"></i>
                        <div class="text-danger fw-semibold fs-8">{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="custom-input-label" for="emailInput">Alamat Email</label>
                        <div class="custom-input-group">
                            <i class="bi bi-envelope input-icon-left"></i>
                            <input type="email" name="email" id="emailInput" class="custom-input-field" value="{{ old('email', 'admin@erp.local') }}" required autofocus placeholder="admin@company.com" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="custom-input-label" for="passwordInput">Password</label>
                        <div class="custom-input-group">
                            <i class="bi bi-lock input-icon-left"></i>
                            <input type="password" name="password" id="passwordInput" class="custom-input-field" value="password" required placeholder="Masukkan password" />
                            <button type="button" class="input-icon-right" onclick="togglePasswordVisibility()" title="Tampilkan/Sembunyikan Kata Sandi">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                            <label class="form-check-label fs-8 text-muted fw-semibold" for="rememberMe">
                                Ingat saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit-primary">
                        <span>Masuk ke Sistem</span>
                        <i class="bi bi-arrow-right fs-6"></i>
                    </button>
                </form>

                <div class="demo-accounts-wrapper">
                    <div class="fs-9 text-muted fw-bold text-uppercase mb-2">Akun Demo Cepat</div>
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <button type="button" class="demo-chip" onclick="fillLogin('admin@erp.local')">
                            <i class="bi bi-shield-check text-primary me-0.5"></i> Admin
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('finance@erp.local')">
                            <i class="bi bi-wallet2 text-success me-0.5"></i> Finance
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('purchasing@erp.local')">
                            <i class="bi bi-cart-check text-warning me-0.5"></i> Purchasing
                        </button>
                        <button type="button" class="demo-chip" onclick="fillLogin('sales@erp.local')">
                            <i class="bi bi-bag-check text-info me-0.5"></i> Sales
                        </button>
                    </div>
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
            document.getElementById('liveClockTime').textContent = `${hours}:${minutes}:${seconds}`;

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
