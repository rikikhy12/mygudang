<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Dokumen') - MYGUDANG ERP</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ asset('assets/fonts/stylesheet.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #eff6ff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --table-header: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'GT Walsheim Pro', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: var(--text-main);
            font-size: 13px;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Bar for Screen Navigation */
        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }

        .btn-back {
            background-color: #f8fafc;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-back:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }

        /* Printable Paper Page Container */
        .page-container {
            max-width: 820px;
            margin: 24px auto 48px auto;
            background: #ffffff;
            padding: 40px 48px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            position: relative;
        }

        /* Header / Letterhead */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e293b;
            margin-bottom: 24px;
        }

        .company-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .company-logo-badge {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 8px;
            color: #ffffff;
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.3px;
            text-transform: uppercase;
        }

        .company-tagline {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .company-address {
            margin-top: 6px;
            font-size: 11.5px;
            color: #475569;
            line-height: 1.4;
            max-width: 380px;
        }

        .doc-meta {
            text-align: right;
        }

        .doc-title {
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .doc-number {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            font-family: monospace;
            background: #f1f5f9;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .doc-status-badge {
            display: inline-block;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }

        /* Party Information Boxes */
        .party-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .party-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
        }

        .party-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #64748b;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .party-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .party-detail {
            font-size: 11.5px;
            color: #475569;
            line-height: 1.4;
        }

        /* Main Data Table */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12.5px;
        }

        .doc-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #94a3b8;
        }

        .doc-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
            vertical-align: top;
        }

        .doc-table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Summary & Totals Block */
        .summary-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            margin-bottom: 30px;
        }

        .notes-box {
            flex: 1;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            font-size: 11.5px;
            color: #475569;
        }

        .notes-box strong {
            display: block;
            color: #0f172a;
            margin-bottom: 4px;
            font-size: 12px;
        }

        .terbilang-text {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            font-style: italic;
            color: #1e3a8a;
            font-weight: 600;
        }

        .totals-table {
            width: 300px;
            border-collapse: collapse;
            font-size: 12.5px;
        }

        .totals-table td {
            padding: 6px 8px;
        }

        .totals-table tr.grand-total {
            border-top: 2px solid #1e293b;
            border-bottom: 2px solid #1e293b;
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            background-color: #eff6ff;
        }

        /* Signatures Section */
        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            text-align: center;
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .sig-box {
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
        }

        .sig-title {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 60px;
        }

        .sig-name {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            min-width: 140px;
            padding-bottom: 2px;
        }

        .sig-role {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Footer Stamp / Notice */
        .doc-footer {
            margin-top: 36px;
            padding-top: 14px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            color: #94a3b8;
        }

        /* Print Media Styles */
        @media print {
            body {
                background: #ffffff;
                font-size: 11pt;
            }

            .print-toolbar {
                display: none !important;
            }

            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 12mm 12mm 12mm 12mm;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Action Bar -->
    <div class="print-toolbar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="javascript:window.history.back()" class="btn-action btn-back">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span style="font-weight: 700; color: #1e293b; font-size: 14px;">
                @yield('toolbar_title', 'Pratinjau Dokumen Cetak')
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="bi bi-printer-fill"></i> Cetak Dokumen (Ctrl+P)
            </button>
        </div>
    </div>

    <!-- Paper Container -->
    <div class="page-container">
        
        <!-- Header / Letterhead -->
        <div class="doc-header">
            <div>
                <div class="company-brand">
                    <img src="{{ asset('assets/media/logos/Logosg.png') }}" alt="Rizal Store Logo" style="width: 50px; height: 50px; object-fit: contain; margin-right: 12px;">
                    <div>
                        <div class="company-name">RIZAL STORE - MYGUDANG ERP</div>
                        <div class="company-tagline">Supplies Daging & Enterprise Logistics</div>
                    </div>
                </div>
                <div class="company-address">
                    Jl. Jenderal Sudirman Kav. 52-53, SCBD, Jakarta Selatan 12190<br>
                    Telp: (021) 555-0198 | Email: billing@mygudang.com | NPWP: 01.234.567.8-012.000
                </div>
            </div>

            <div class="doc-meta">
                <div class="doc-title">@yield('doc_title', 'FAKTUR')</div>
                <div class="doc-number">@yield('doc_number', '-')</div>
                <div>
                    @yield('doc_status')
                </div>
            </div>
        </div>

        @yield('doc_content')

        <!-- Document Footer -->
        <div class="doc-footer">
            <span>Dicetak secara otomatis oleh sistem MYGUDANG ERP pada {{ date('d/m/Y H:i:s') }}</span>
            <span>Halaman 1 dari 1</span>
        </div>
    </div>

</body>
</html>
