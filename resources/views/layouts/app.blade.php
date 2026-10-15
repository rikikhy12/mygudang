<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Dashboard') | MYGUDANG ERP</title>
    <!-- Brand Favicon & Logo -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 128 128'%3E%3Cdefs%3E%3ClinearGradient id='bgGrad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%230B132B'/%3E%3Cstop offset='100%25' stop-color='%23111C38'/%3E%3C/linearGradient%3E%3ClinearGradient id='blueGrad' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%231D4ED8'/%3E%3Cstop offset='60%25' stop-color='%232563EB'/%3E%3Cstop offset='100%25' stop-color='%2338BDF8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='128' height='128' rx='28' fill='url(%23bgGrad)' stroke='%2338BDF8' stroke-width='4' stroke-opacity='0.4'/%3E%3Cg transform='translate(6, 6)'%3E%3Cpath d='M14 84V26C14 20 19 16 26 19L48 38L70 19C77 16 82 20 82 26V52' fill='none' stroke='url(%23blueGrad)' stroke-width='14' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M108 34a34 34 0 0 0-24-11 38 38 0 0 0-38 38v0a38 38 0 0 0 38 38 36 36 0 0 0 27-12' fill='none' stroke='%23FFFFFF' stroke-width='13' stroke-linecap='round'/%3E%3Cpath d='M78 50L68 60L78 70' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M83 72L92 48' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round'/%3E%3Cpath d='M97 50L107 60L97 70' stroke='%2338BDF8' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3Crect x='110' y='14' width='12' height='12' rx='3' fill='%2338BDF8'/%3E%3Crect x='124' y='14' width='12' height='12' rx='3' fill='%232563EB'/%3E%3Crect x='117' y='28' width='14' height='14' rx='3.5' fill='%2360A5FA'/%3E%3C/g%3E%3C/svg%3E">
    
    <!-- Font Family: GT Walsheim Pro & Fallbacks -->
    <link href="{{ asset('assets/fonts/stylesheet.css') }}" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    
    <!-- Metronic Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --font-sans: 'GT Walsheim Pro', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --font-heading: 'GT Walsheim Pro', 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --sidebar-width: 285px;
            --header-height: 72px;
            --sidebar-bg: #0d1117;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active: #2563eb;
            --surface-bg: #f4f6fa;
            --card-border: #e2e8f0;
        }

        body {
            font-family: var(--font-sans) !important;
            font-size: 14.5px !important;
            line-height: 1.55;
            background-color: var(--surface-bg);
            color: #1e293b;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            letter-spacing: -0.011em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6, .fw-bolder, .fw-bold, .card-title, .modal-title, .page-heading {
            font-family: var(--font-heading) !important;
            letter-spacing: -0.02em;
        }

        /* Fixed Dark Obsidian Sidebar Styling */
        #kt_app_sidebar {
            width: var(--sidebar-width) !important;
            background-color: var(--sidebar-bg) !important;
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            z-index: 1050 !important;
            display: flex !important;
            flex-direction: column !important;
            border-right: 1px solid rgba(255, 255, 255, 0.07) !important;
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.3) !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.3s ease !important;
        }

        .sidebar-logo {
            height: var(--header-height);
            min-height: var(--header-height);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.28);
        }

        .sidebar-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 18px 12px 35px;
        }

        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.18);
            border-radius: 4px;
        }

        .menu-section-title {
            font-family: var(--font-heading) !important;
            color: #64748b;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 16px 14px 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 10px 14px;
            border-radius: 0.6rem;
            margin-bottom: 3px;
            color: #cbd5e1;
            font-weight: 500;
            font-size: 0.94rem;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-link i {
            font-size: 1.22rem;
            color: #94a3b8;
            transition: all 0.2s ease;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background-color: var(--sidebar-hover);
        }
        .sidebar-link:hover i {
            color: #60a5fa;
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0px 4px 14px 0px rgba(37, 99, 235, 0.4);
        }
        .sidebar-link.active i {
            color: #ffffff !important;
        }

        /* Topbar Header */
        #kt_app_header {
            margin-left: var(--sidebar-width) !important;
            width: calc(100% - var(--sidebar-width)) !important;
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.02);
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        /* Main Content Container */
        #kt_app_main {
            margin-left: var(--sidebar-width) !important;
            width: calc(100% - var(--sidebar-width)) !important;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease, width 0.3s ease;
        }

        #kt_app_content {
            flex: 1;
            padding: 24px 28px;
        }

        /* Footer */
        #kt_app_footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 28px;
        }

        /* Collapsed Sidebar State (Desktop & Mobile) */
        body.sidebar-collapsed #kt_app_sidebar {
            transform: translateX(-100%) !important;
        }
        body.sidebar-collapsed #kt_app_header,
        body.sidebar-collapsed #kt_app_main {
            margin-left: 0 !important;
            width: 100% !important;
        }

        /* Mobile Viewport Breakpoint (< 992px) */
        @media (max-width: 991.98px) {
            #kt_app_sidebar {
                transform: translateX(-100%) !important;
            }
            body.mobile-sidebar-open #kt_app_sidebar {
                transform: translateX(0) !important;
            }
            #kt_app_header, #kt_app_main {
                margin-left: 0 !important;
                width: 100% !important;
                padding-left: 16px;
                padding-right: 16px;
            }
            #kt_app_content {
                padding: 16px 12px;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0; left: 0; right: 0; bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 1040;
                backdrop-filter: blur(3px);
            }
            body.mobile-sidebar-open .sidebar-backdrop {
                display: block;
            }
        }

        /* Modern Card Styling */
        .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.85rem !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02) !important;
        }

        /* DataTables Metronic Custom Styling */
        .dataTables_wrapper {
            font-size: 0.90rem;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 14px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 6px 13px !important;
            border-radius: 8px !important;
            margin: 0 2px !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #475569 !important;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eff6ff !important;
            color: #2563eb !important;
            border-color: #93c5fd !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #2563eb !important;
            color: #ffffff !important;
            border: 1px solid #2563eb !important;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }
        /* DataTables Column Search Styling */
        .column-search-row th {
            background-color: #f8fafc !important;
            padding: 4px 6px !important;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        .column-search-row input {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            font-size: 0.76rem !important;
            font-weight: 500 !important;
            padding: 4px 8px !important;
            width: 100% !important;
            height: 28px !important;
            color: #1e293b !important;
            transition: all 0.2s ease !important;
        }
        .column-search-row input:focus {
            outline: none !important;
            border-color: #2563eb !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
        }
        div.dataTables_wrapper div.dataTables_length {
            display: inline-flex;
            align-items: center;
        }
        div.dataTables_wrapper div.dataTables_length label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 0;
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Metronic Form Solid & Select2 Solid SM Polish */
        .form-control-solid, .form-select-solid {
            background-color: #f5f8fa !important;
            border-color: #f5f8fa !important;
            color: #181c32 !important;
            transition: color .2s ease, background-color .2s ease;
        }
        .form-control-solid:focus, .form-select-solid:focus {
            background-color: #eef3f7 !important;
            border-color: #eef3f7 !important;
            color: #181c32 !important;
            outline: none !important;
        }
        .form-control-sm, .form-select-sm {
            min-height: calc(1.5em + 0.95rem + 2px) !important;
            padding: 0.475rem 0.75rem !important;
            font-size: 0.825rem !important;
            border-radius: 0.475rem !important;
        /* Card Header & Title Subtitle Polish */
        .card-header .card-title.flex-column,
        .card-title.flex-column {
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important;
            justify-content: center !important;
        }
        .card-header .card-title.flex-column > span,
        .card-title.flex-column > span {
            display: block !important;
            margin-top: 0.25rem !important;
        }
        .badge.badge-circle {
            width: 24px;
            height: 24px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50% !important;
        }

        /* Select2 Solid Integration */
        .select2-container--bootstrap5 .select2-selection--single,
        .select2-container .select2-selection--single {
            background-color: #f5f8fa !important;
            border-color: #f5f8fa !important;
            border-radius: 0.475rem !important;
            height: calc(1.5em + 0.95rem + 2px) !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered,
        .select2-container .select2-selection--single .select2-selection__rendered {
            color: #181c32 !important;
            font-size: 0.825rem !important;
            padding-left: 0.75rem !important;
            line-height: normal !important;
        }
        .select2-container--bootstrap5 .select2-selection--single .select2-selection__arrow,
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 8px !important;
        }
        div.dataTables_wrapper div.dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 4px 8px;
            background: #ffffff;
            font-size: 0.82rem;
            height: 32px;
            color: #334155;
            cursor: pointer;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e2e8f0 !important;
            color: #64748b;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            font-weight: 700;
            padding: 13px 12px !important;
            background: #f8fafc;
        }
        table.dataTable tbody td {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 13px 12px !important;
            vertical-align: middle;
        }
        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }
    </style>
    @yield('styles')
</head>

<body>
    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar()"></div>

    <!-- App Root -->
    <div class="d-flex flex-column flex-root">
        
        <!-- App Sidebar (Left Fixed) -->
        <aside id="kt_app_sidebar">
            
            <!-- Logo Section -->
            <div class="sidebar-logo">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                    <div class="bg-white rounded-3 p-1 me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                        <img src="{{ asset('assets/media/logos/Logosg.png') }}" alt="Rizal Store Logo" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div>
                        <div class="text-white fw-bolder fs-4 lh-1" style="font-family: var(--font-heading) !important; letter-spacing: -0.01em;">MYGUDANG ERP</div>
                        <span class="text-muted fs-8 fw-bold text-uppercase" style="letter-spacing: 0.1em; color: #94a3b8 !important;">Rizal Store</span>
                    </div>
                </a>
                <button class="btn btn-icon btn-sm btn-active-color-primary d-lg-none text-white" onclick="toggleSidebar()">
                    <i class="bi bi-x fs-2"></i>
                </button>
            </div>

            <!-- Menu Scroll Area (Dynamic from Database) -->
            <div class="sidebar-scroll">
                @if(isset($dbMenus) && $dbMenus->isNotEmpty())
                    @php
                        $groupedMenus = $dbMenus->groupBy('section');
                    @endphp
                    @foreach($groupedMenus as $sectionName => $menuItems)
                        @php
                            $visibleItems = $menuItems->filter(function($item) {
                                if ($item->permission_name === 'role:admin') {
                                    return auth()->check() && auth()->user()->hasRole('admin');
                                }
                                return true;
                            });
                        @endphp
                        @if($visibleItems->isNotEmpty())
                            @if($sectionName)
                                <div class="menu-section-title">{{ $sectionName }}</div>
                            @endif
                            @foreach($visibleItems as $menu)
                                <a href="{{ $menu->resolved_url }}" class="sidebar-link {{ $menu->isActiveRoute() ? 'active' : '' }}">
                                    @if($menu->icon)
                                        <i class="bi {{ $menu->icon }}"></i>
                                    @else
                                        <i class="bi bi-circle"></i>
                                    @endif
                                    <span>{{ $menu->name }}</span>
                                    @if($menu->badge)
                                        <span class="badge {{ $menu->badge_color ?? 'badge-light-primary' }} fs-9 ms-auto">{{ $menu->badge }}</span>
                                    @endif
                                </a>
                            @endforeach
                        @endif
                    @endforeach
                @else
                    <div class="menu-section-title">Menu Utama</div>
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Executive Dashboard</span>
                    </a>
                @endif
            </div>
        </aside>

        <!-- App Header (Topbar) -->
        <header id="kt_app_header">
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-icon btn-light-primary btn-sm me-3" onclick="toggleSidebar()" title="Buka/Tutup Menu Sidebar">
                    <i class="bi bi-list fs-2"></i>
                </button>
                <h1 class="text-gray-900 fw-bolder fs-4 my-0 d-none d-sm-block">
                    @yield('page_title', 'ERP Dashboard')
                </h1>
            </div>

            <!-- Windows 10 / 11 Search Bar Trigger -->
            <div class="d-none d-md-flex align-items-center ms-4 flex-grow-1" style="max-width: 440px;">
                <div class="d-flex align-items-center justify-content-between w-100 px-3 py-2 rounded-2 shadow-xs cursor-pointer" 
                    onclick="openSpotlightSearch()" 
                    title="Windows Search (Ctrl + K)"
                    style="background: #f8fafc; border: 1px solid #cbd5e1; border-bottom: 2px solid #2563eb; cursor: pointer; transition: all 0.2s ease;">
                    <div class="d-flex align-items-center gap-2 text-gray-700">
                        <i class="bi bi-search text-primary fs-6"></i>
                        <span class="fs-7 text-gray-600 fw-normal">Type here to search / Cari menu & transaksi...</span>
                    </div>
                    <kbd class="badge bg-white text-gray-700 border border-gray-300 shadow-xs px-2 py-1 fs-9 fw-bold">Ctrl + K</kbd>
                </div>
            </div>

            <!-- Mobile Quick Search Button -->
            <button type="button" class="btn btn-icon btn-light-primary btn-sm d-md-none ms-auto me-2" onclick="openSpotlightSearch()" title="Pencarian Cepat (Ctrl + K)">
                <i class="bi bi-search fs-5"></i>
            </button>

            <!-- User Menu & Quick Shortcuts -->
            <div class="d-flex align-items-center gap-2 ms-md-auto">
                @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('maintenance.backup', ['format' => 'sql']) }}" class="btn btn-sm btn-light-success fw-bold px-3 py-2 d-none d-lg-flex align-items-center" title="Unduh Cadangan Database (.sql)">
                    <i class="bi bi-cloud-arrow-down-fill me-1"></i> Backup
                </a>
                @endif

                <div class="d-flex align-items-center ms-1">
                    <div class="symbol symbol-35px symbol-circle me-2 bg-light-primary text-primary fw-bolder fs-6 d-flex align-items-center justify-content-center">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="d-none d-md-block text-start">
                        <div class="fw-bolder text-gray-800 fs-7 lh-1">{{ auth()->user()->name ?? 'User' }}</div>
                        <span class="badge badge-light-primary fs-9 fw-bold mt-1">
                            {{ auth()->user()->roles->pluck('display_name')->first() ?? 'Staff' }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline ms-1">
                    @csrf
                    <button type="submit" class="btn btn-icon btn-light-danger btn-sm" title="Keluar dari Sistem">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- App Main Area -->
        <main id="kt_app_main">
            <div id="kt_app_content">
                
                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="alert alert-dismissible bg-light-success d-flex align-items-center p-4 mb-6 border border-success border-dashed">
                        <i class="bi bi-check-circle-fill fs-2 text-success me-3"></i>
                        <div class="d-flex flex-column">
                            <span class="text-success fw-bold">{{ session('success') }}</span>
                        </div>
                        <button type="button" class="btn btn-icon btn-sm ms-auto" data-bs-dismiss="alert">
                            <i class="bi bi-x fs-2 text-success"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-dismissible bg-light-danger d-flex align-items-center p-4 mb-6 border border-danger border-dashed">
                        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-3"></i>
                        <div class="d-flex flex-column">
                            <span class="text-danger fw-bold">{{ session('error') }}</span>
                        </div>
                        <button type="button" class="btn btn-icon btn-sm ms-auto" data-bs-dismiss="alert">
                            <i class="bi bi-x fs-2 text-danger"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-dismissible bg-light-danger d-flex align-items-center p-4 mb-6 border border-danger border-dashed">
                        <i class="bi bi-x-circle-fill fs-2 text-danger me-3"></i>
                        <div class="d-flex flex-column">
                            <strong class="text-danger">Perhatian:</strong>
                            <ul class="mb-0 ps-3 text-danger fs-7">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="btn btn-icon btn-sm ms-auto" data-bs-dismiss="alert">
                            <i class="bi bi-x fs-2 text-danger"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>

            <footer id="kt_app_footer">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
                    <span class="text-muted fs-7">{{ date('Y') }} &copy; <strong class="text-gray-800">MYGUDANG ERP</strong></span>
                    <span class="badge badge-light-primary fw-bold">Metronic 8 Integrated</span>
                </div>
            </footer>
        </main>

    </div>

    <!-- Metronic Javascript Bundles -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <script>
        function toggleSidebar() {
            if (window.innerWidth < 992) {
                document.body.classList.toggle('mobile-sidebar-open');
            } else {
                document.body.classList.toggle('sidebar-collapsed');
            }
        }

        // Direct Shortcuts: Alt+A (Tagihan AP), Alt+R (Faktur AR)
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key.toLowerCase() === 'a') {
                e.preventDefault();
                window.location.href = "{{ route('procurement.invoices.index') }}";
            } else if (e.altKey && e.key.toLowerCase() === 'r') {
                e.preventDefault();
                window.location.href = "{{ route('sales.invoices.index') }}";
            }
        });

        // Initialize Select2 across all elements with data-control="select2"
        $(document).ready(function() {
            if ($.fn.select2) {
                $('[data-control="select2"]').each(function() {
                    const el = $(this);
                    if (!el.hasClass("select2-hidden-accessible")) {
                        const placeholder = el.data('placeholder') || el.attr('placeholder') || 'Pilih Opsi...';
                        const allowClear = el.data('allow-clear') !== undefined ? Boolean(el.data('allow-clear')) : true;
                        const hideSearch = Boolean(el.data('hide-search'));
                        const modalParent = el.closest('.modal');
                        
                        el.select2({
                            placeholder: placeholder,
                            allowClear: allowClear,
                            minimumResultsForSearch: hideSearch ? Infinity : 0,
                            dropdownParent: modalParent.length ? modalParent : null,
                            width: '100%'
                        });
                    }
                });
            }
        });

        // Initialize DataTables with automatic Column Header Search
        $(document).ready(function() {
            if ($.fn.DataTable) {
                // Suppress browser alert popups from DataTables
                $.fn.dataTable.ext.errMode = 'none';

                $('.datatable, .dataTable').each(function() {
                    const tableEl = $(this);

                    // Skip dynamic repeaters, lookups, modals, print layouts, and excluded tables
                    if (this.id === 'itemsTable' || this.id === 'stepsTable' || this.id === 'bomItemsTable' || 
                        this.id === 'cartTable' || this.id === 'soTable' || this.id === 'poTable' || this.id === 'lookupTable' || 
                        this.closest('#depositModal') || this.closest('#paymentModal') || this.closest('#shiftModal') ||
                        tableEl.hasClass('no-datatable-init') || tableEl.hasClass('no-datatable') || tableEl.closest('.print-container').length > 0) {
                        return;
                    }

                    // Skip tables with empty-state colspan rows or mismatched columns to avoid DT errors
                    const emptyColspan = tableEl.find('tbody tr td[colspan]').length > 0;
                    if (emptyColspan) {
                        return;
                    }

                    // Skip tables with server-side pagination already present below
                    if (tableEl.closest('.card-body').find('.pagination, nav[role="navigation"]').length > 0 || 
                        tableEl.parent().next('.px-4').find('.pagination, nav[role="navigation"]').length > 0) {
                        return;
                    }

                    if (!$.fn.DataTable.isDataTable(this)) {
                        tableEl.DataTable({
                            language: {
                                lengthMenu: "Tampilkan _MENU_ baris",
                                info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ data",
                                infoEmpty: "Tidak ada data yang tersedia",
                                zeroRecords: "Tidak ditemukan data yang sesuai",
                                paginate: {
                                    first: "Awal",
                                    last: "Akhir",
                                    next: '<i class="bi bi-chevron-right"></i>',
                                    previous: '<i class="bi bi-chevron-left"></i>'
                                }
                            },
                            responsive: true,
                            pageLength: 10,
                            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                            order: [], // Preserve initial server order
                            columnDefs: [
                                { targets: 'no-sort', orderable: false }
                            ],
                            // Top row has no length/search; length, info, and pagination placed cleanly at bottom
                            dom: "<'table-responsive'tr>" +
                                 "<'row mt-3 align-items-center'<'col-md-6 d-flex align-items-center gap-3 flex-wrap text-muted fs-7'li><'col-md-6 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0'p>>",
                            initComplete: function () {
                                const api = this.api();
                                const thead = $(api.table().header()).parent();
                                
                                if (thead.find('tr.column-search-row').length === 0) {
                                    const searchRow = $('<tr class="column-search-row bg-light"></tr>');
                                    
                                    api.columns().every(function (colIdx) {
                                        const column = this;
                                        const th = $(column.header());
                                        const isNoSort = th.hasClass('no-sort') || th.text().toLowerCase().includes('aksi') || th.text().trim() === '';
                                        
                                        const cell = $('<th class="py-1 px-2 border-bottom"></th>');
                                        if (!isNoSort) {
                                            const headerText = th.text().trim();
                                            const input = $(`<input type="text" class="form-control form-control-sm fs-8 py-1 px-2 border-gray-300" placeholder="Filter ${headerText}..." style="height: 28px; font-weight: normal; font-size: 0.76rem; border-radius: 6px; width: 100%; min-width: 70px;" />`);
                                            
                                            input.on('keyup change clear input', function () {
                                                if (column.search() !== this.value) {
                                                    column.search(this.value).draw();
                                                }
                                            });
                                            
                                            input.on('click', function(e) {
                                                e.stopPropagation();
                                            });
                                            
                                            cell.append(input);
                                        }
                                        searchRow.append(cell);
                                    });
                                    
                                    // Append search row directly under header row
                                    $(api.table().header()).after(searchRow);
                                }
                            }
                        });
                    }
                });
            }
        });
    </script>

    @include('partials.spotlight_modal')
    @include('partials.quick_modals')

    @yield('scripts')
</body>
</html>
