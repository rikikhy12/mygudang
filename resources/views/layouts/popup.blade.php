<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Form Input') - MYGUDANG ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />

    <!-- Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />

    <!-- Metronic Global Custom CSS -->
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14.5px;
            background-color: #f4f6f9;
            color: #1e293b;
            padding: 20px 24px;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6, .fw-bolder, .fw-bold, .card-title {
            font-family: 'Outfit', 'Inter', sans-serif !important;
        }
        .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.85rem !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04) !important;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <!-- Minimal Top Indicator -->
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <div class="w-32px h-32px rounded-2 d-flex align-items-center justify-content-center text-white shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);">
                    <i class="bi bi-grid-fill fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-gray-900 m-0">@yield('page_title', 'Form Input Data')</h5>
                    <span class="text-muted fs-8">Mode Window Opener Transaksi ERP</span>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light-danger fw-bold" onclick="window.close()">
                <i class="bi bi-x-lg me-1"></i> Tutup Jendela
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center p-3 mb-4 rounded-3 fs-7">
                <i class="bi bi-check-circle-fill fs-4 text-success me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 rounded-3 fs-7">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger p-3 mb-4 rounded-3 fs-7">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-circle me-1"></i> Terdapat kesalahan input:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
