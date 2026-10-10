<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <title>Data Tersimpan - Menutup Jendela</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-4">
    <div class="card shadow border-0 p-5 text-center rounded-4" style="max-width: 480px; width: 100%;">
        <div class="mb-3">
            <div class="w-70px h-70px rounded-circle bg-light-success text-success d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                <i class="bi bi-check-circle-fill fs-1 text-success"></i>
            </div>
        </div>
        <h4 class="fw-bold text-gray-900 mb-2">{{ $title ?? 'Data Berhasil Disimpan!' }}</h4>
        <p class="text-muted fs-7 mb-4">Mengirim data ke formulir transaksi dan menutup jendela popup otomatis...</p>
        <div class="d-flex align-items-center justify-content-center gap-2 text-primary fs-7 fw-semibold">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            <span>Memproses callback window opener...</span>
        </div>
    </div>

    <script>
        const payload = @json($payload);
        
        function sendToOpener() {
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.postMessage(payload, '*');
                    if (typeof window.opener.handleWindowOpenerCallback === 'function') {
                        window.opener.handleWindowOpenerCallback(payload);
                    }
                } catch(e) {
                    console.error('Window Opener error:', e);
                }
                setTimeout(() => {
                    window.close();
                }, 400);
            } else {
                setTimeout(() => {
                    window.location.href = "{{ $redirectUrl ?? '/' }}";
                }, 1000);
            }
        }

        window.onload = sendToOpener;
    </script>
</body>
</html>
