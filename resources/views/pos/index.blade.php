@extends('layouts.app')

@section('title', 'Point of Sale (POS Kasir)')
@section('page_title', 'Point of Sale - Terminal Kasir')

@section('styles')
<style>
    #kt_app_content {
        padding: 8px 12px !important;
        max-width: 100% !important;
    }
    .pos-product-card {
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 0.85rem;
    }
    .pos-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.09);
        border-color: #2563eb;
    }
    .pos-cart-container {
        position: sticky;
        top: 75px;
    }
    .pos-cart-items {
        max-height: 400px;
        overflow-y: auto;
    }
    .quick-cash-btn {
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">
    <!-- Top Action Bar & Branch / Shift Management -->
    <div class="card shadow-sm border-0 mb-3 bg-white">
        <div class="card-body p-3 d-flex flex-column gap-2">
            <!-- Row 1: Branch Switcher, Shift Status & Global Controls -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                
                <!-- Left: Branch Dropdown & Shift Pill -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <!-- Branch Switcher Form -->
                    <form action="{{ route('pos.branch.switch') }}" method="POST" class="d-flex align-items-center m-0" id="posBranchForm">
                        @csrf
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-light-primary text-primary fw-bold">
                                <i class="bi bi-shop"></i>
                            </span>
                            <select name="branch_id" class="form-select form-select-sm fw-bold fs-8 border-gray-300" onchange="document.getElementById('posBranchForm').submit()" {{ $activeShift ? 'disabled title="Tutup kasir terlebih dahulu untuk berpindah cabang"' : '' }}>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $currentBranchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->code }} - {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>

                    <!-- Shift Status Badge -->
                    @if($activeShift)
                        <span class="badge badge-light-success border border-success fw-bolder fs-7 py-2 px-3 d-inline-flex align-items-center gap-1">
                            <span class="w-8px h-8px rounded-circle bg-success d-inline-block"></span>
                            Shift Aktif: #{{ $activeShift->shift_number }}
                        </span>
                        <span class="badge badge-light-info fw-bold fs-8 py-2 px-2 d-none d-md-inline-block" title="Akun Kas Laci Kasir">
                            <i class="bi bi-wallet2 text-info me-1"></i> {{ $activeShift->cashAccount->name ?? 'Kas Kasir' }}
                        </span>
                    @else
                        <span class="badge badge-light-danger border border-danger fw-bolder fs-7 py-2 px-3 d-inline-flex align-items-center gap-1">
                            <span class="w-8px h-8px rounded-circle bg-danger d-inline-block"></span>
                            Kasir Belum Buka Shift
                        </span>
                    @endif

                    <span class="badge badge-light-secondary text-gray-700 fw-bold fs-8 py-2 px-2 d-none d-lg-inline-block">
                        <i class="bi bi-person-badge text-primary me-1"></i> {{ auth()->user()->name }}
                    </span>
                </div>

                <!-- Right: Shift Action Buttons & POS Tools -->
                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                    @if($activeShift)
                        <!-- Setor Kas Drop Button -->
                        <button type="button" class="btn btn-sm btn-light-warning fw-bold py-1 px-3 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dropCashModal" title="Setor uang dari kas laci ke rekening bank/kas utama">
                            <i class="bi bi-box-arrow-up-right fs-6"></i> Setor Kas / Drop
                        </button>
                        <!-- Tutup Kasir Button -->
                        <button type="button" class="btn btn-sm btn-danger fw-bolder py-1 px-3 d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#closeShiftModal">
                            <i class="bi bi-lock-fill fs-6"></i> Tutup Kasir
                        </button>
                    @else
                        <!-- Buka Kasir Button -->
                        <button type="button" class="btn btn-sm btn-success fw-bolder py-1 px-4 shadow-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                            <i class="bi bi-key-fill fs-6"></i> Buka Kasir (Start Shift)
                        </button>
                    @endif

                    <button type="button" class="btn btn-sm btn-light-success fw-bold py-1 px-2" onclick="openQuickItemModal(handlePosNewItem)" title="Tambah Barang Cepat">
                        <i class="bi bi-plus-lg"></i> + Item
                    </button>
                    <a href="{{ route('procurement.invoices.index') }}" class="btn btn-sm btn-light-danger fw-bold py-1 px-2 d-none d-sm-inline-flex" title="Tagihan Hutang Supplier (AP Invoice)">
                        <i class="bi bi-receipt me-1"></i> Tagihan AP
                    </a>
                    <a href="{{ route('sales.invoices.index') }}" class="btn btn-sm btn-light-primary fw-bold py-1 px-2 d-none d-sm-inline-flex" title="Faktur Piutang Pelanggan (AR Invoice)">
                        <i class="bi bi-receipt-cutoff me-1"></i> Faktur AR
                    </a>
                    <button type="button" class="btn btn-sm btn-light-info fw-bold py-1 px-2" onclick="togglePosFullscreen()" title="Mode Layar Penuh">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light-secondary fw-bold py-1 px-2">
                        <i class="bi bi-speedometer2"></i>
                    </a>
                </div>
            </div>

            <!-- Row 2 (If shift is active): Shift Live Metrics Bar -->
            @if($activeShift)
            <div class="d-flex align-items-center gap-3 pt-2 border-top border-gray-200 flex-wrap fs-8">
                <div class="d-flex align-items-center gap-1 text-gray-700">
                    <span class="text-muted">Modal Awal:</span>
                    <strong class="text-gray-900">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</strong>
                </div>
                <div class="text-muted">&bull;</div>
                <div class="d-flex align-items-center gap-1 text-success">
                    <span class="text-muted">Jual Tunai:</span>
                    <strong class="text-success">+Rp {{ number_format($activeShift->total_cash_sales, 0, ',', '.') }}</strong>
                </div>
                <div class="text-muted">&bull;</div>
                <div class="d-flex align-items-center gap-1 text-info">
                    <span class="text-muted">Non-Tunai:</span>
                    <strong class="text-info">Rp {{ number_format($activeShift->total_non_cash_sales, 0, ',', '.') }}</strong>
                </div>
                <div class="text-muted">&bull;</div>
                <div class="d-flex align-items-center gap-1 text-danger">
                    <span class="text-muted">Setoran Kas:</span>
                    <strong class="text-danger">-Rp {{ number_format($activeShift->total_drops, 0, ',', '.') }}</strong>
                </div>
                <div class="text-muted">&bull;</div>
                <div class="d-flex align-items-center gap-1 text-primary ms-auto">
                    <span class="text-muted">Estimasi Kas di Laci:</span>
                    <strong class="fs-7 fw-bolder text-primary">Rp {{ number_format($activeShift->closing_cash_expected, 0, ',', '.') }}</strong>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if(!$activeShift)
    <!-- Prominent Alert: Kasir Belum Buka Shift -->
    <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex flex-column flex-md-row p-4 mb-3 align-items-md-center justify-content-between shadow-xs">
        <div class="d-flex align-items-center mb-3 mb-md-0">
            <div class="symbol symbol-45px symbol-circle bg-danger bg-opacity-10 me-3 d-flex align-items-center justify-content-center">
                <i class="bi bi-exclamation-triangle-fill fs-2x text-danger"></i>
            </div>
            <div>
                <h4 class="text-danger fw-bolder mb-1">Kasir Belum Buka Shift!</h4>
                <div class="fs-7 text-gray-700">
                    Sesi kasir untuk cabang <strong>{{ $currentBranch->code }} - {{ $currentBranch->name }}</strong> belum aktif. Silakan lakukan <strong>Buka Kasir (Start Shift)</strong> untuk memuat kas cabang, harga cabang, stok gudang cabang, dan mengaktifkan transaksi kasir.
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-danger fw-bolder btn-sm px-4 py-2 text-nowrap d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#openShiftModal">
            <i class="bi bi-key-fill fs-5"></i> Buka Kasir Sekarang
        </button>
    </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Catalog & Item Search -->
        <div class="col-lg-7 col-xl-8">
            <!-- Top Filter Bar -->
            <div class="card card-flush shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-7">
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-gray-500 fs-5"></i>
                                <input type="text" id="posSearchInput" class="form-control form-control-sm ps-10" placeholder="Scan Barcode atau ketik SKU / Nama Produk..." autofocus />
                            </div>
                        </div>
                        <div class="col-md-5 d-flex gap-2">
                            <select id="posWarehouseSelect" class="form-select form-select-sm" title="Gudang Pengeluaran Kasir" onchange="window.location.href = '{{ route('pos.index') }}?warehouse_id=' + this.value">
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" {{ $defaultWarehouse && $defaultWarehouse->id == $w->id ? 'selected' : '' }}>
                                        🏬 Gudang: {{ $w->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Category Filter Pills -->
                    <div class="d-flex flex-wrap gap-1 mt-2 pt-2 border-top">
                        <button type="button" class="btn btn-xs btn-sm btn-primary category-filter-btn py-1 px-3" data-category-id="">
                            Semua
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" class="btn btn-xs btn-sm btn-light-primary category-filter-btn py-1 px-3" data-category-id="{{ $cat->id }}">
                                {{ $cat->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="row g-3" id="posProductGrid">
                @forelse($items as $item)
                @php
                    $isService = !$item->isPhysical();
                    $isBundle = $item->isBundle();
                    $allowChangePrice = (bool) ($item->allow_change_price ?? false);
                    $itemPrice = $item->branch_price ?? (float) $item->selling_price;
                    $itemCost = $item->branch_cost ?? (float) $item->purchase_price;
                    $margin = $item->branch_margin ?? $item->getEffectiveMinMarginPercent();
                    $minPrice = $item->branch_min_price ?? 0;
                    $itemStock = $item->branch_stock ?? (float) $item->current_stock;
                @endphp
                <div class="col-6 col-md-4 col-xl-3 product-item-col" data-id="{{ $item->id }}" data-category="{{ $item->category_id }}" data-name="{{ strtolower($item->name) }}" data-code="{{ strtolower($item->code) }}" data-barcode="{{ strtolower($item->barcode ?? '') }}">
                    <div class="pos-product-card p-2 h-100 d-flex flex-column justify-content-between rounded-3 border" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->code }}', {{ $itemPrice }}, {{ $itemStock }}, '{{ $item->unit->symbol ?? '' }}', {{ $isService ? 'true' : 'false' }}, {{ $minPrice }}, {{ $margin }}, {{ $isBundle ? 'true' : 'false' }}, {{ $allowChangePrice ? 'true' : 'false' }})">
                        <div>
                            <!-- Product Image / Icon Thumbnail -->
                            <div class="w-100 rounded-2 mb-2 overflow-hidden position-relative bg-light d-flex align-items-center justify-content-center" style="height: 100px;">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-100 h-100 object-fit-cover" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                    <div class="d-none w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-box-seam fs-2 text-gray-400"></i>
                                    </div>
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted bg-light-primary bg-opacity-30">
                                        <i class="bi {{ $isService ? 'bi-wrench-adjustable' : ($isBundle ? 'bi-collection' : 'bi-box-seam') }} fs-1 text-primary opacity-50"></i>
                                    </div>
                                @endif
                                
                                <span class="badge position-absolute top-0 start-0 m-1 {{ $itemStock > 0 ? 'badge-light-success bg-white bg-opacity-90 text-success' : 'badge-light-danger bg-white bg-opacity-90 text-danger' }} fs-9 fw-bold shadow-xs">
                                    @if($isService)
                                        Jasa
                                    @elseif($isBundle)
                                        Paket
                                    @else
                                        {{ number_format($itemStock, 0) }} {{ $item->unit->symbol ?? '' }}
                                    @endif
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge badge-light-secondary fs-9 fw-bold">{{ $item->code }}</span>
                                <span class="text-muted fs-9 text-truncate" style="max-width: 90px;">{{ $item->category->name ?? 'Umum' }}</span>
                            </div>
                            <h4 class="fs-7 fw-bold text-gray-800 mb-0 text-truncate" title="{{ $item->name }}">{{ $item->name }}</h4>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <div>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-primary fw-bolder fs-6">Rp {{ number_format($itemPrice, 0, ',', '.') }}</span>
                                    @if($allowChangePrice)
                                        <span class="badge badge-light-success fs-9 py-0 px-1" title="Harga jual fleksibel (Bisa diubah)"><i class="bi bi-pencil-fill text-success" style="font-size: 8px;"></i></span>
                                    @endif
                                </div>
                                @if(!$isService && !$isBundle && $margin > 0)
                                    <span class="badge badge-light-warning fs-9 p-0" title="Limit Margin: {{ number_format($margin, 0) }}%">
                                        Min Rp {{ number_format($minPrice, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                            <button type="button" class="btn btn-icon btn-light-primary btn-sm rounded-circle shadow-xs" title="Tambah ke Keranjang">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-10 text-muted">
                    <i class="bi bi-box fs-2tx mb-2"></i>
                    <div class="fw-bold">Belum ada data barang aktif untuk cabang ini.</div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: POS Cart & Checkout Panel -->
        <div class="col-lg-5 col-xl-4">
            <div class="card card-flush shadow-sm pos-cart-container">
                <div class="card-header pt-3 pb-2 min-h-auto">
                    <div class="card-title d-flex flex-column">
                        <h3 class="fw-bold text-gray-800 fs-5 mb-0">
                            <i class="bi bi-cart3 text-primary me-2"></i> Keranjang Kasir
                        </h3>
                        <span class="text-muted fs-8" id="cartItemCountText">0 Item dipilih</span>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-danger py-1 px-2" onclick="clearCart()" title="Kosongkan Keranjang">
                            <i class="bi bi-trash"></i> Reset
                        </button>
                    </div>
                </div>

                <div class="card-body pt-2 pb-3">
                    <!-- Customer Selection & Deposit Status -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-8 fw-bold text-gray-700 m-0">Pelanggan</label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-9 text-success fw-bold" id="customerDepositText">Saldo Deposit: Rp 0</span>
                                <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-bold fs-9" onclick="openQuickContactModal('customer', '#posCustomerSelect')">
                                    <i class="bi bi-person-plus-fill me-1"></i> + Customer
                                </button>
                            </div>
                        </div>
                        <select id="posCustomerSelect" class="form-select form-select-sm" onchange="onCustomerChanged()">
                            <option value="{{ $walkinCustomer->id }}" data-deposit="0">{{ $walkinCustomer->name }}</option>
                            @foreach($customers as $c)
                                @if($c->id != $walkinCustomer->id)
                                    <option value="{{ $c->id }}" data-deposit="{{ $c->getDepositBalance() }}">
                                        {{ $c->name }} ({{ $c->code }}) - Dep: Rp {{ number_format($c->getDepositBalance(), 0, ',', '.') }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Items List -->
                    <div class="pos-cart-items mb-2 border rounded p-2 bg-light">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table" id="cartTable">
                            <tbody id="cartTableBody">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-8">
                                        <i class="bi bi-cart-x fs-2x text-gray-400 d-block mb-2"></i>
                                        Keranjang masih kosong.<br>Klik produk di sebelah kiri.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Calculation Summary (Tax in Percentage %) -->
                    <div class="bg-light p-3 rounded border border-gray-300 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fs-7">Subtotal:</span>
                            <span class="text-gray-900 fw-bold fs-7" id="cartSubtotal">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fs-7">Diskon (Rp):</span>
                            <input type="number" step="1" min="0" id="cartDiscountInput" class="form-control form-control-sm text-end w-120px py-1" value="0" oninput="calculateCartTotals()">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center gap-1">
                                <span class="text-muted fs-7">Pajak PPN (%):</span>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 fs-9" onclick="setTaxRate(0)">0%</button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 fs-9" onclick="setTaxRate(11)">11%</button>
                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-1 fs-9" onclick="setTaxRate(12)">12%</button>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="number" step="0.1" min="0" id="cartTaxRateInput" class="form-control form-control-sm text-end w-60px py-1" value="0" oninput="calculateCartTotals()">
                                <span class="fs-8 fw-bold">%</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted fs-8">Nilai PPN:</span>
                            <span class="text-gray-700 fw-semibold fs-8" id="cartTaxAmountDisplay">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-400">
                            <span class="text-gray-900 fw-bolder fs-6">Grand Total:</span>
                            <span class="text-primary fw-bolder fs-4" id="cartGrandTotal">Rp 0</span>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    @if(!$activeShift)
                        <button type="button" class="btn btn-danger w-100 py-3 fw-bolder fs-6 shadow-sm" data-bs-toggle="modal" data-bs-target="#openShiftModal">
                            <i class="bi bi-key-fill fs-4 me-1"></i> Buka Kasir Terlebih Dahulu
                        </button>
                    @else
                        <button type="button" class="btn btn-primary w-100 py-3 fw-bolder fs-6 shadow-sm" id="btnOpenPayment" onclick="openPaymentModal()" disabled>
                            <i class="bi bi-cash-coin fs-4 me-1"></i> Bayar Sekarang (F4)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pembayaran POS -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header py-3 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="symbol symbol-35px symbol-circle bg-light-primary">
                        <span class="symbol-label text-primary"><i class="bi bi-wallet2 fs-4"></i></span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-gray-900 m-0">Pembayaran Kasir</h5>
                        <span class="text-muted fs-8">Pilih metode tunggal atau pecah pembayaran (Split Bill / Multi-Payment)</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Mode Switcher Tabs -->
                <ul class="nav nav-pills nav-pills-custom mb-4 d-flex gap-2" id="paymentModeTabs" role="tablist">
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link btn btn-outline btn-outline-primary active w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="single-pay-tab" data-bs-toggle="pill" data-bs-target="#single-pay-pane" type="button" role="tab" onclick="setPaymentMode('single')">
                            <i class="bi bi-credit-card-2-front"></i> Pembayaran Tunggal (Single)
                        </button>
                    </li>
                    <li class="nav-item flex-fill" role="presentation">
                        <button class="nav-link btn btn-outline btn-outline-info w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="split-pay-tab" data-bs-toggle="pill" data-bs-target="#split-pay-pane" type="button" role="tab" onclick="setPaymentMode('split')">
                            <i class="bi bi-diagram-2"></i> Split / Multi-Payment (Pecah Nominal)
                        </button>
                    </li>
                </ul>

                <!-- Total Display -->
                <div class="card bg-light-primary border border-primary border-dashed p-3 text-center mb-4">
                    <span class="text-muted fs-8 fw-bold text-uppercase">Total Tagihan Transaksi</span>
                    <span class="text-primary fw-bolder fs-2hx" id="modalGrandTotal">Rp 0</span>
                </div>

                <div class="tab-content" id="paymentModeContent">
                    <!-- Tab 1: Single Payment -->
                    <div class="tab-pane fade show active" id="single-pay-pane" role="tabpanel">
                        <!-- Payment Method -->
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold text-gray-700">Pilih Metode Pembayaran</label>
                            <div class="row g-2">
                                <div class="col-6 col-sm-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="payCash" value="cash" checked onchange="togglePayMethod()">
                                    <label class="btn btn-outline btn-outline-primary btn-sm w-100 fw-bold py-2" for="payCash">
                                        <i class="bi bi-cash me-1"></i> Tunai (Cash)
                                    </label>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="payTransfer" value="transfer" onchange="togglePayMethod()">
                                    <label class="btn btn-outline btn-outline-primary btn-sm w-100 fw-bold py-2" for="payTransfer">
                                        <i class="bi bi-bank me-1"></i> Transfer
                                    </label>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="payQris" value="qris" onchange="togglePayMethod()">
                                    <label class="btn btn-outline btn-outline-primary btn-sm w-100 fw-bold py-2" for="payQris">
                                        <i class="bi bi-qr-code me-1"></i> QRIS
                                    </label>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <input type="radio" class="btn-check" name="payment_method" id="payDeposit" value="deposit" onchange="togglePayMethod()">
                                    <label class="btn btn-outline btn-outline-success btn-sm w-100 fw-bold py-2" for="payDeposit">
                                        <i class="bi bi-piggy-bank me-1"></i> Saldo Deposit
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Deposit Notification Info -->
                        <div id="depositInfoSection" class="alert bg-light-success border border-success border-dashed p-3 mb-3 d-none">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill text-success fs-3 me-2"></i>
                                <div>
                                    <div class="fw-bold text-success fs-7" id="modalDepositCustomerName">Pelanggan</div>
                                    <span class="fs-8 text-gray-700" id="modalDepositBalanceText">Sisa Saldo Deposit: Rp 0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Kas / Bank Account -->
                        <div class="mb-3" id="accountSelectGroup">
                            <label class="form-label fs-7 fw-bold text-gray-700" id="accountLabel">Akun Kas / Bank Penerima</label>
                            <select id="modalAccountSelect" class="form-select form-select-sm">
                                @foreach($cashAccounts as $acc)
                                    <option value="{{ $acc->id }}" 
                                        data-type="{{ str_contains(strtolower($acc->name), 'bank') ? 'bank' : 'cash' }}"
                                        {{ ($activeShift && $activeShift->cash_account_id == $acc->id) ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name }} {{ ($activeShift && $activeShift->cash_account_id == $acc->id) ? '(Kas Cabang Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cash Payment Inputs -->
                        <div id="cashPaymentSection">
                            <div class="mb-3">
                                <label class="form-label fs-7 fw-bold text-gray-700">Uang Diterima (Rp)</label>
                                <input type="number" id="paidAmountInput" class="form-control form-control-solid fs-5 fw-bold text-end" placeholder="0" oninput="calculateChange()">
                            </div>

                            <!-- Quick Cash Buttons -->
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <button type="button" class="btn btn-light-secondary btn-sm quick-cash-btn" onclick="setQuickCash('exact')">Uang Pas</button>
                                <button type="button" class="btn btn-light-primary btn-sm quick-cash-btn" onclick="setQuickCash(10000)">10rb</button>
                                <button type="button" class="btn btn-light-primary btn-sm quick-cash-btn" onclick="setQuickCash(20000)">20rb</button>
                                <button type="button" class="btn btn-light-primary btn-sm quick-cash-btn" onclick="setQuickCash(50000)">50rb</button>
                                <button type="button" class="btn btn-light-primary btn-sm quick-cash-btn" onclick="setQuickCash(100000)">100rb</button>
                                <button type="button" class="btn btn-light-primary btn-sm quick-cash-btn" onclick="setQuickCash(200000)">200rb</button>
                            </div>

                            <!-- Change Display -->
                            <div class="d-flex justify-content-between align-items-center p-3 rounded bg-light border border-gray-300">
                                <span class="fs-6 fw-bold text-gray-700">Kembalian:</span>
                                <span class="fs-4 fw-bolder text-success" id="changeAmountDisplay">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Split / Multi-Payment -->
                    <div class="tab-pane fade" id="split-pay-pane" role="tabpanel">
                        <div class="alert alert-light-primary border border-primary border-dashed py-2 px-3 mb-3 d-flex align-items-center">
                            <i class="bi bi-info-circle-fill text-primary fs-4 me-2"></i>
                            <span class="fs-8 text-gray-800">
                                Kasir dapat memecah pembayaran ke beberapa metode/bank (misal: <strong>Tunai Rp 50.000 + Transfer BCA Rp 100.000 + QRIS</strong>).
                            </span>
                        </div>

                        <!-- Split Payment Rows Container -->
                        <div id="splitPaymentRowsContainer" class="d-flex flex-column gap-2 mb-3">
                            <!-- Dynamic split rows injected via JS -->
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-light-primary fw-bold" onclick="addSplitPaymentRow()">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Metode Pembayaran Lain
                            </button>
                            <span class="fs-8 text-muted" id="splitRowCountText">2 Metode</span>
                        </div>

                        <!-- Split Payment Balance Summary -->
                        <div class="p-3 bg-light rounded border border-gray-300">
                            <div class="row g-2 text-center align-items-center">
                                <div class="col-4">
                                    <span class="text-muted fs-8 d-block fw-semibold">Total Tagihan</span>
                                    <span class="text-gray-900 fw-bold fs-7" id="splitSummaryTotal">Rp 0</span>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted fs-8 d-block fw-semibold">Total Terbayar</span>
                                    <span class="text-primary fw-bold fs-7" id="splitSummaryPaid">Rp 0</span>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted fs-8 d-block fw-semibold" id="splitSummaryStatusLabel">Sisa Tagihan</span>
                                    <span class="fw-bold fs-7 text-danger" id="splitSummaryDiff">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-3 bg-light">
                <button type="button" class="btn btn-light btn-sm fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-sm fw-bolder px-5" id="btnSubmitCheckout" onclick="processCheckout()">
                    <i class="bi bi-check2-circle me-1"></i> Selesaikan Transaksi
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Struk Thermal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title fw-bold text-gray-800">
                    <i class="bi bi-receipt text-success me-1"></i> Transaksi Berhasil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <i class="bi bi-check-circle-fill text-success fs-3tx mb-3"></i>
                <div class="fw-bold fs-6 mb-1 text-gray-800">Penjualan Kasir Sukses!</div>
                <div class="text-muted fs-8 mb-4" id="receiptInvoiceNo">POS-20260913-0001</div>

                <iframe id="receiptFrame" src="" style="width: 100%; height: 350px; border: 1px solid #e2e8f0; border-radius: 6px;"></iframe>
            </div>
            <div class="modal-footer justify-content-between py-2">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-sm fw-bold" onclick="printReceiptFrame()">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buka Kasir (Start Shift) -->
<div class="modal fade" id="openShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form action="{{ route('pos.shift.open') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-door-open-fill fs-4"></i> Buka Sesi Kasir Baru (Start Shift)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-primary d-flex align-items-center mb-4 py-2 px-3">
                        <i class="bi bi-info-circle fs-3 text-primary me-2"></i>
                        <div class="fs-8 text-primary">
                            Buka sesi kasir untuk mencatat kas awal laci dan melacak seluruh transaksi penjualan & setoran kas per shift.
                        </div>
                    </div>

                    <!-- Pilih Cabang -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-gray-800 required fs-7">Cabang Kasir Operasional</label>
                        <select name="branch_id" class="form-select form-select-solid fw-bold" required onchange="onShiftBranchChange(this)">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" 
                                    data-cash-name="{{ $b->cashAccount ? '[' . $b->cashAccount->code . '] ' . $b->cashAccount->name : 'Kas Utama' }}"
                                    data-cash-id="{{ $b->cash_account_id ?? '' }}"
                                    data-deposit-name="{{ $b->depositAccount ? '[' . $b->depositAccount->code . '] ' . $b->depositAccount->name : '-' }}"
                                    {{ $currentBranchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->code }} - {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="text-muted fs-8 mt-1">Pilih cabang toko/outlet tempat Anda bertugas saat ini.</div>
                    </div>

                    <!-- Akun Kas Terikat Pada Cabang (Auto dari Pengaturan Cabang) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-gray-800 fs-7">Akun Kas Laci Kasir (Sesuai Pengaturan Cabang)</label>
                        <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-gray-900 fs-7 d-flex align-items-center gap-2">
                                    <i class="bi bi-wallet2 text-success fs-5"></i>
                                    <span id="openShiftBranchCashName">{{ $currentBranch->cashAccount ? '[' . $currentBranch->cashAccount->code . '] ' . $currentBranch->cashAccount->name : 'Kas Utama (Default)' }}</span>
                                </div>
                                <div class="text-muted fs-9 mt-1">Akun kas kasir dikonfigurasi otomatis dari Master Cabang.</div>
                            </div>
                            <a href="{{ route('branches.index') }}" target="_blank" class="btn btn-xs btn-light-primary fw-bold" title="Ubah Akun Kas di Master Cabang">
                                <i class="bi bi-gear-fill"></i> Atur
                            </a>
                        </div>
                        <input type="hidden" name="cash_account_id" id="openShiftCashAccountId" value="{{ $currentBranch->cash_account_id ?? '' }}">
                    </div>

                    <!-- Modal Awal (Starting Cash Float) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-gray-800 required fs-7">Modal Kas Awal di Laci (Uang Kembalian)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" name="starting_cash" class="form-control form-control-solid fw-bolder fs-5 text-gray-900" value="0" min="0" step="500" required>
                        </div>
                        <div class="text-muted fs-8 mt-1">Uang tunai pecahan kecil yang sudah disiapkan di laci untuk kembalian.</div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-gray-700 fs-7">Catatan Pembukaan Shift (Opsional)</label>
                        <textarea name="notes" class="form-control form-control-solid fs-7" rows="2" placeholder="Contoh: Shift Pagi Kasir 1..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-3 bg-light">
                    <button type="button" class="btn btn-light fw-bold btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bolder btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Buka Kasir Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($activeShift)
<!-- Modal Setor Kas / Cash Drop -->
<div class="modal fade" id="dropCashModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <form action="{{ route('pos.shift.drop') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark py-3">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-up-right fs-4 text-dark"></i> Setoran Kas Kasir (Cash Drop)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Shift Info Banner -->
                    <div class="bg-light-warning border border-warning rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold fs-7 text-gray-800">Shift #{{ $activeShift->shift_number }} ({{ $activeShift->branch->name ?? '-' }})</div>
                            <div class="text-muted fs-8">Dari Akun: <span class="fw-bold text-dark">{{ $activeShift->cashAccount->name ?? 'Kas Kasir' }}</span></div>
                        </div>
                        <div class="text-end">
                            <div class="fs-8 text-muted">Estimasi Kas di Laci:</div>
                            <div class="fw-bolder fs-6 text-warning">Rp {{ number_format($activeShift->estimated_cash, 0, ',', '.') }}</div>
                        </div>
                    </div>

                    <!-- Rekening Tujuan Setoran -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-gray-800 required fs-7">Setor Kas Ke Rekening (Kas / Bank Tujuan)</label>
                        <select name="to_account_id" class="form-select form-select-solid fw-bold" required>
                            @foreach($cashAccounts as $acc)
                                @if($acc->id != $activeShift->cash_account_id)
                                    <option value="{{ $acc->id }}" {{ (isset($activeShift->branch->deposit_account_id) && $activeShift->branch->deposit_account_id == $acc->id) ? 'selected' : ((stripos($acc->name, 'bca') !== false || stripos($acc->name, 'utama') !== false || stripos($acc->name, 'bank') !== false) ? 'selected' : '') }}>
                                        [{{ $acc->code }}] {{ $acc->name }} {{ (isset($activeShift->branch->deposit_account_id) && $activeShift->branch->deposit_account_id == $acc->id) ? '(Default Cabang)' : '' }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="text-muted fs-8 mt-1">Pilih Bank atau Kas Utama yang menerima setoran uang tunai kasir ini.</div>
                    </div>

                    <!-- Jumlah Setoran -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-gray-800 required fs-7">Jumlah Uang Disetor</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" name="amount" class="form-control form-control-solid fw-bolder fs-5 text-gray-900" min="1000" step="500" max="{{ max(0, $activeShift->estimated_cash) }}" required placeholder="0">
                        </div>
                        <div class="text-muted fs-8 mt-1">Maksimal setoran: Rp {{ number_format(max(0, $activeShift->estimated_cash), 0, ',', '.') }} (Sesuai uang di laci).</div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-gray-700 fs-7">Keterangan / Berita Setoran (Opsional)</label>
                        <input type="text" name="notes" class="form-control form-control-solid fs-7" placeholder="Contoh: Setoran siang ke Bank BCA oleh Pak Budi">
                    </div>
                </div>
                <div class="modal-footer py-3 bg-light">
                    <button type="button" class="btn btn-light fw-bold btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bolder btn-sm px-4">
                        <i class="bi bi-check2-circle me-1"></i> Proses Setoran Kas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tutup Kasir (End Shift / Z-Report) -->
<div class="modal fade" id="closeShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0">
            <form action="{{ route('pos.shift.close') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                        <i class="bi bi-lock-fill fs-4"></i> Tutup Kasir & Rekonsiliasi (End Shift / Z-Report)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Shift Summary Cards -->
                    <div class="row g-2 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted fs-8 fw-semibold">Modal Awal</div>
                                <div class="fw-bolder fs-7 text-gray-800">Rp {{ number_format($activeShift->starting_cash, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted fs-8 fw-semibold">Penjualan Tunai</div>
                                <div class="fw-bolder fs-7 text-success">+Rp {{ number_format($activeShift->cash_sales, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted fs-8 fw-semibold">Penjualan Non-Tunai</div>
                                <div class="fw-bolder fs-7 text-info">Rp {{ number_format($activeShift->non_cash_sales, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="border rounded p-2 bg-light text-center">
                                <div class="text-muted fs-8 fw-semibold">Total Setoran Kas</div>
                                <div class="fw-bolder fs-7 text-warning">-Rp {{ number_format($activeShift->total_drops, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Expected Cash Highlight -->
                    <div class="card bg-light-primary border-primary border-dashed mb-4">
                        <div class="card-body p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge badge-primary mb-1">Rumus Sistem</span>
                                <div class="fw-bold text-gray-800 fs-7">Estimasi Kas Seharusnya di Laci:</div>
                                <div class="text-muted fs-8">(Modal Awal + Penjualan Tunai - Setoran Kas)</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bolder fs-2 text-primary" id="modalExpectedCashText">Rp {{ number_format($activeShift->estimated_cash, 0, ',', '.') }}</div>
                                <input type="hidden" id="modalExpectedCashVal" value="{{ $activeShift->estimated_cash }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Hitung Fisik Laci Kasir -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-800 required fs-7">Uang Fisik Kasir Sebenarnya di Laci</label>
                            <div class="input-group mb-1">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="closing_cash_actual" id="closingCashActualInput" class="form-control form-control-solid fw-bolder fs-4 text-gray-900" value="{{ $activeShift->estimated_cash }}" min="0" step="500" required oninput="calculateShiftDifference()">
                            </div>
                            <div class="text-muted fs-8">Hitung total uang fisik tunai di laci kasir saat ini.</div>

                            <!-- Live Difference Indicator -->
                            <div class="mt-2 p-2 rounded border" id="shiftDiffContainer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-8 fw-bold text-gray-700">Status Selisih Kas:</span>
                                    <span class="badge badge-success fw-bolder fs-8" id="shiftDiffBadge">Pas (Rp 0)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Opsi Setoran Akhir ke Bank / Kas Utama -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-gray-800 fs-7">Setor Seluruh Uang Kasir ke Rekening:</label>
                            <select name="deposit_to_account_id" class="form-select form-select-solid fw-bold mb-1">
                                <option value="">-- Jangan Setor (Tetap simpan di kas laci) --</option>
                                @foreach($cashAccounts as $acc)
                                    @if($acc->id != $activeShift->cash_account_id)
                                        <option value="{{ $acc->id }}" {{ (isset($activeShift->branch->deposit_account_id) && $activeShift->branch->deposit_account_id == $acc->id) ? 'selected' : '' }}>
                                            Setor ke: [{{ $acc->code }}] {{ $acc->name }} {{ (isset($activeShift->branch->deposit_account_id) && $activeShift->branch->deposit_account_id == $acc->id) ? '(Default Cabang)' : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="text-muted fs-8">Jika dipilih, seluruh sisa fisik uang kasir akan otomatis dijurnal transfer ke rekening tersebut.</div>
                        </div>
                    </div>

                    <!-- Catatan Tutup Kasir -->
                    <div class="mt-3">
                        <label class="form-label fw-bold text-gray-700 fs-7">Catatan Penutupan Shift / Keterangan Selisih (Opsional)</label>
                        <textarea name="notes" class="form-control form-control-solid fs-7" rows="2" placeholder="Contoh: Shift sore ditutup dalam kondisi kas seimbang dan laci terkunci..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-3 bg-light">
                    <button type="button" class="btn btn-light fw-bold btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bolder btn-sm px-4">
                        <i class="bi bi-door-closed-fill me-1"></i> Tutup Kasir & Cetak Z-Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    let cart = [];
    let currentGrandTotal = 0;
    let currentTaxAmount = 0;

    function onShiftBranchChange(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const cashName = opt.getAttribute('data-cash-name') || 'Kas Utama';
        const cashId = opt.getAttribute('data-cash-id') || '';
        
        const nameEl = document.getElementById('openShiftBranchCashName');
        if (nameEl) {
            nameEl.innerText = cashName;
        }
        const inputEl = document.getElementById('openShiftCashAccountId');
        if (inputEl) {
            inputEl.value = cashId;
        }
    }

    function calculateShiftDifference() {
        const expected = parseFloat(document.getElementById('modalExpectedCashVal')?.value || 0);
        const actual = parseFloat(document.getElementById('closingCashActualInput')?.value || 0);
        const diff = actual - expected;
        const badge = document.getElementById('shiftDiffBadge');
        if (!badge) return;

        if (diff === 0) {
            badge.className = 'badge badge-success fw-bolder fs-8';
            badge.innerText = 'Pas (Rp 0)';
        } else if (diff > 0) {
            badge.className = 'badge badge-primary fw-bolder fs-8';
            badge.innerText = `Lebih (+${formatRupiah(diff)})`;
        } else {
            badge.className = 'badge badge-danger fw-bolder fs-8';
            badge.innerText = `Kurang (${formatRupiah(diff)})`;
        }
    }

    // Automatically collapse sidebar on POS load for maximum screen space!
    document.addEventListener('DOMContentLoaded', () => {
        document.body.classList.add('sidebar-collapsed');
        onCustomerChanged();
    });

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function togglePosFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log('Error fullscreen:', err.message);
            });
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    }

    function onCustomerChanged() {
        const custSelect = document.getElementById('posCustomerSelect');
        const opt = custSelect.options[custSelect.selectedIndex];
        const deposit = parseFloat(opt.getAttribute('data-deposit') || 0);
        document.getElementById('customerDepositText').innerText = `Saldo Deposit: ${formatRupiah(deposit)}`;
    }

    function setTaxRate(rate) {
        document.getElementById('cartTaxRateInput').value = rate;
        calculateCartTotals();
    }

    const hasActiveShift = {{ $activeShift ? 'true' : 'false' }};
    const activeShiftCashAccountId = {{ $activeShift ? ($activeShift->cash_account_id ?: 'null') : 'null' }};

    function promptOpenShift() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Buka Kasir Diperlukan',
                text: 'Sesi kasir belum dibuka. Mohon buka kasir (Start Shift) terlebih dahulu untuk cabang {{ $currentBranch->name }}!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-key-fill me-1"></i> Buka Kasir Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-primary fw-bold',
                    cancelButton: 'btn btn-light fw-bold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const modalEl = document.getElementById('openShiftModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
            });
        } else {
            alert('Sesi kasir belum dibuka. Mohon buka kasir (Start Shift) terlebih dahulu!');
            const modalEl = document.getElementById('openShiftModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
    }

    function addToCart(id, name, code, price, stock, unit, isService, minPrice = 0, margin = 0, isBundle = false, allowChangePrice = false) {
        if (!hasActiveShift) {
            promptOpenShift();
            return;
        }

        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (!isService && !isBundle && existing.qty >= stock) {
                alert('Stok barang tidak mencukupi untuk menambah jumlah!');
                return;
            }
            existing.qty = Math.round((existing.qty + 1) * 100) / 100;
        } else {
            if (!isService && !isBundle && stock <= 0) {
                alert('Stok barang ini habis di gudang cabang aktif!');
                return;
            }
            cart.push({
                id: id,
                name: name,
                code: code,
                price: price,
                qty: 1,
                koli: 1,
                stock: stock,
                unit: unit,
                isService: isService,
                isBundle: isBundle,
                minPrice: minPrice,
                margin: margin,
                allowChangePrice: Boolean(allowChangePrice)
            });
        }
        renderCart();
    }

    function setCartPrice(id, val) {
        const item = cart.find(i => i.id === id);
        if (item && item.allowChangePrice) {
            const newPrice = Math.max(0, parseFloat(val) || 0);
            if (item.minPrice > 0 && newPrice < item.minPrice) {
                alert(`Harga jual '${item.name}' minimal Rp ${Number(item.minPrice).toLocaleString('id-ID')} (Limit Margin ${item.margin}%)`);
                item.price = item.minPrice;
            } else {
                item.price = newPrice;
            }
            renderCart();
        }
    }

    function updateCartPriceLive(id, val) {
        const item = cart.find(i => i.id === id);
        if (item && item.allowChangePrice) {
            const newPrice = Math.max(0, parseFloat(val) || 0);
            item.price = newPrice;
            const row = document.getElementById(`cart_row_${id}`);
            if (row) {
                const lineTotal = item.qty * item.price;
                const lineTotalEl = row.querySelector('.line-total-cell');
                if (lineTotalEl) lineTotalEl.innerText = formatRupiah(lineTotal);
            }
            calculateCartTotals();
        }
    }

    function updateCartQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (item) {
            const newQty = Math.round((item.qty + delta) * 100) / 100;
            if (newQty <= 0) {
                removeFromCart(id);
            } else {
                if (!item.isService && !item.isBundle && newQty > item.stock) {
                    alert('Stok barang hanya tersedia ' + item.stock + ' ' + item.unit);
                    return;
                }
                item.qty = newQty;
                renderCart();
            }
        }
    }

    function setCartQty(id, val) {
        const item = cart.find(i => i.id === id);
        if (item) {
            const newQty = parseFloat(val) || 0;
            if (newQty <= 0) {
                removeFromCart(id);
            } else {
                if (!item.isService && !item.isBundle && newQty > item.stock) {
                    alert('Stok barang hanya tersedia ' + item.stock + ' ' + item.unit);
                    item.qty = item.stock;
                } else {
                    item.qty = newQty;
                }
                renderCart();
            }
        }
    }

    function updateCartKoli(id, val) {
        const item = cart.find(i => i.id === id);
        if (item) {
            item.koli = val !== '' ? (parseFloat(val) || 0) : null;
            renderCartSummary();
        }
    }

    function removeFromCart(id) {
        cart = cart.filter(i => i.id !== id);
        renderCart();
    }

    function clearCart() {
        if (cart.length > 0 && confirm('Kosongkan semua item di keranjang?')) {
            cart = [];
            renderCart();
        }
    }

    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        const btnPay = document.getElementById('btnOpenPayment');

        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-8">
                        <i class="bi bi-cart-x fs-2x text-gray-400 d-block mb-2"></i>
                        Keranjang masih kosong.<br>Klik produk di sebelah kiri.
                    </td>
                </tr>
            `;
            btnPay.disabled = true;
            renderCartSummary();
            calculateCartTotals();
            return;
        }

        let html = '';
        cart.forEach(item => {
            const lineTotal = item.qty * item.price;
            html += `
                <tr class="align-top border-bottom border-gray-200" id="cart_row_${item.id}">
                    <td class="ps-1 py-2">
                        <div class="fw-bold text-gray-800 text-truncate" style="max-width: 125px;" title="${item.name}">${item.name}</div>
                        ${item.allowChangePrice ? `
                            <div class="d-flex align-items-center gap-1 mt-1">
                                <span class="fs-9 fw-bold text-success">Rp</span>
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end fw-bold py-0 px-1 border-success text-success bg-light-success bg-opacity-25" style="width: 82px; height: 22px; font-size: 11px;" value="${item.price}" onchange="setCartPrice(${item.id}, this.value)" oninput="updateCartPriceLive(${item.id}, this.value)" title="Ketik untuk ubah harga jual satuan">
                                <span class="badge badge-light-success p-0 fs-9" title="Bisa Ubah Harga"><i class="bi bi-pencil-fill text-success" style="font-size: 9px;"></i></span>
                            </div>
                        ` : `
                            <div class="text-muted fs-9">${formatRupiah(item.price)} / ${item.unit || 'Satuan'}</div>
                        `}
                        <div class="d-flex align-items-center gap-1 mt-1">
                            <span class="fs-9 fw-bold text-primary">Koli:</span>
                            <input type="number" min="0" step="1" class="form-control form-control-sm text-center py-0 px-1 border-primary" style="width: 48px; height: 22px; font-size: 11px;" value="${item.koli !== null && item.koli !== undefined ? item.koli : ''}" placeholder="-" oninput="updateCartKoli(${item.id}, this.value)" title="Jumlah Box / Koli">
                        </div>
                    </td>
                    <td class="text-center py-2">
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-icon btn-light btn-xs p-0 w-20px h-20px rounded" onclick="updateCartQty(${item.id}, -1)">
                                <i class="bi bi-dash fs-8"></i>
                            </button>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-sm text-center p-0 fw-bold fs-7" style="width: 52px; height: 24px;" value="${item.qty}" onchange="setCartQty(${item.id}, this.value)" title="Kuantitas (KG / Satuan)">
                            <button type="button" class="btn btn-icon btn-light btn-xs p-0 w-20px h-20px rounded" onclick="updateCartQty(${item.id}, 1)">
                                <i class="bi bi-plus fs-8"></i>
                            </button>
                        </div>
                        <div class="fs-9 text-muted mt-0.5">${item.unit || ''}</div>
                    </td>
                    <td class="text-end fw-bold text-gray-900 py-2 line-total-cell">${formatRupiah(lineTotal)}</td>
                    <td class="text-end pe-1 py-2">
                        <button type="button" class="btn btn-icon btn-light-danger btn-xs p-0 w-20px h-20px" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-x fs-7"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        btnPay.disabled = false;
        renderCartSummary();
        calculateCartTotals();
    }

    function renderCartSummary() {
        const countText = document.getElementById('cartItemCountText');
        if (cart.length === 0) {
            countText.innerText = '0 Item dipilih';
            return;
        }
        let totalQty = 0;
        let totalKoli = 0;
        cart.forEach(item => {
            totalQty += (parseFloat(item.qty) || 0);
            if (item.koli) totalKoli += (parseFloat(item.koli) || 0);
        });

        let text = `${totalQty % 1 === 0 ? totalQty : totalQty.toFixed(2)} Qty (${cart.length} Item)`;
        if (totalKoli > 0) {
            text += ` &bull; <span class="badge badge-light-primary text-primary fw-bold fs-8">${totalKoli} Koli</span>`;
        }
        countText.innerHTML = text;
    }

    function calculateCartTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += (item.qty * item.price);
        });

        const discount = parseFloat(document.getElementById('cartDiscountInput').value) || 0;
        const taxRate = parseFloat(document.getElementById('cartTaxRateInput').value) || 0;
        const taxable = Math.max(0, subtotal - discount);
        currentTaxAmount = Math.round(taxable * (taxRate / 100));
        currentGrandTotal = taxable + currentTaxAmount;

        document.getElementById('cartSubtotal').innerText = formatRupiah(subtotal);
        document.getElementById('cartTaxAmountDisplay').innerText = formatRupiah(currentTaxAmount);
        document.getElementById('cartGrandTotal').innerText = formatRupiah(currentGrandTotal);
    }

    let currentPaymentMode = 'single';
    const cashAccountsList = @json($cashAccounts);

    function setPaymentMode(mode) {
        currentPaymentMode = mode;
        if (mode === 'split') {
            const container = document.getElementById('splitPaymentRowsContainer');
            if (container.children.length === 0) {
                initDefaultSplitRows();
            } else {
                calculateSplitPaymentTotals();
            }
        }
    }

    function initDefaultSplitRows() {
        const container = document.getElementById('splitPaymentRowsContainer');
        container.innerHTML = '';
        
        // Find default cash account from active shift or fallback to general cash
        const defaultCash = activeShiftCashAccountId
            ? cashAccountsList.find(a => a.id == activeShiftCashAccountId)
            : (cashAccountsList.find(a => (a.name || '').toLowerCase().includes('kas')) || cashAccountsList[0]);
        const defaultBank = cashAccountsList.find(a => (a.name || '').toLowerCase().includes('bank') || (a.name || '').toLowerCase().includes('bca') || (a.name || '').toLowerCase().includes('mandiri')) || cashAccountsList[0];

        const half = Math.floor(currentGrandTotal / 2);
        const remainder = currentGrandTotal - half;

        addSplitPaymentRow('cash', defaultCash ? defaultCash.id : '', half, '');
        addSplitPaymentRow('transfer', defaultBank ? defaultBank.id : '', remainder, '');
    }

    function addSplitPaymentRow(defaultMethod = 'transfer', defaultAccId = '', defaultAmount = 0, defaultRef = '') {
        const container = document.getElementById('splitPaymentRowsContainer');
        const rowId = 'split_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        
        let accountOptionsHtml = '';
        cashAccountsList.forEach(acc => {
            const isSelected = defaultAccId && defaultAccId == acc.id ? 'selected' : '';
            const isBank = (acc.name || '').toLowerCase().includes('bank');
            accountOptionsHtml += `<option value="${acc.id}" data-type="${isBank ? 'bank' : 'cash'}" ${isSelected}>${acc.code} - ${acc.name}</option>`;
        });

        const rowDiv = document.createElement('div');
        rowDiv.className = 'card border border-gray-300 p-3 bg-white shadow-none split-payment-row';
        rowDiv.id = rowId;
        rowDiv.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-md-3 col-6">
                    <label class="form-label fs-9 fw-bold text-gray-700 m-0">Metode</label>
                    <select class="form-select form-select-sm split-method-select" onchange="onSplitMethodChanged('${rowId}')">
                        <option value="cash" ${defaultMethod === 'cash' ? 'selected' : ''}>💵 Tunai (Cash)</option>
                        <option value="transfer" ${defaultMethod === 'transfer' ? 'selected' : ''}>🏦 Transfer Bank</option>
                        <option value="qris" ${defaultMethod === 'qris' ? 'selected' : ''}>📱 QRIS</option>
                        <option value="deposit" ${defaultMethod === 'deposit' ? 'selected' : ''}>🐷 Saldo Deposit</option>
                    </select>
                </div>
                <div class="col-md-3 col-6 split-acc-col">
                    <label class="form-label fs-9 fw-bold text-gray-700 m-0">Akun Kas / Bank</label>
                    <select class="form-select form-select-sm split-account-select">
                        ${accountOptionsHtml}
                    </select>
                </div>
                <div class="col-md-3 col-7">
                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <label class="form-label fs-9 fw-bold text-gray-700 m-0">Nominal (Rp)</label>
                        <button type="button" class="btn btn-xs btn-link p-0 text-primary fw-bold fs-9" onclick="fillSplitRemaining('${rowId}')" title="Isi sisa kekurangan tagihan">
                            <i class="bi bi-magic me-0.5"></i> Isi Sisa
                        </button>
                    </div>
                    <input type="number" min="0" step="100" class="form-control form-control-sm text-end fw-bold split-amount-input" value="${defaultAmount}" oninput="calculateSplitPaymentTotals()">
                </div>
                <div class="col-md-2 col-3">
                    <label class="form-label fs-9 fw-bold text-gray-700 m-0">Ref / Ket</label>
                    <input type="text" class="form-control form-control-sm split-ref-input" placeholder="No. Ref / Bank" value="${defaultRef}">
                </div>
                <div class="col-md-1 col-2 text-end pt-3">
                    <button type="button" class="btn btn-icon btn-light-danger btn-sm w-30px h-30px rounded-circle" onclick="removeSplitPaymentRow('${rowId}')" title="Hapus Baris">
                        <i class="bi bi-trash fs-7"></i>
                    </button>
                </div>
            </div>
        `;

        container.appendChild(rowDiv);
        onSplitMethodChanged(rowId);
        calculateSplitPaymentTotals();
    }

    function removeSplitPaymentRow(rowId) {
        const container = document.getElementById('splitPaymentRowsContainer');
        if (container.children.length <= 1) {
            alert('Minimal harus ada 1 metode pembayaran.');
            return;
        }
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            calculateSplitPaymentTotals();
        }
    }

    function onSplitMethodChanged(rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
        const method = row.querySelector('.split-method-select').value;
        const accCol = row.querySelector('.split-acc-col');
        const accSelect = row.querySelector('.split-account-select');

        if (method === 'deposit') {
            accCol.style.visibility = 'hidden';
        } else {
            accCol.style.visibility = 'visible';
            // Auto select matching cash or bank account
            const targetType = method === 'cash' ? 'cash' : 'bank';
            for (let i = 0; i < accSelect.options.length; i++) {
                if (accSelect.options[i].getAttribute('data-type') === targetType) {
                    accSelect.selectedIndex = i;
                    break;
                }
            }
        }
        calculateSplitPaymentTotals();
    }

    function fillSplitRemaining(targetRowId) {
        const rows = document.querySelectorAll('#splitPaymentRowsContainer .split-payment-row');
        let currentTotalOther = 0;
        rows.forEach(row => {
            if (row.id !== targetRowId) {
                const amt = parseFloat(row.querySelector('.split-amount-input').value) || 0;
                currentTotalOther += amt;
            }
        });

        const needed = Math.max(0, currentGrandTotal - currentTotalOther);
        const targetRow = document.getElementById(targetRowId);
        if (targetRow) {
            targetRow.querySelector('.split-amount-input').value = needed;
            calculateSplitPaymentTotals();
        }
    }

    function calculateSplitPaymentTotals() {
        const rows = document.querySelectorAll('#splitPaymentRowsContainer .split-payment-row');
        let totalPaid = 0;
        let depositTotal = 0;

        rows.forEach(row => {
            const amt = parseFloat(row.querySelector('.split-amount-input').value) || 0;
            const method = row.querySelector('.split-method-select').value;
            totalPaid += amt;
            if (method === 'deposit') {
                depositTotal += amt;
            }
        });

        const count = rows.length;
        document.getElementById('splitRowCountText').innerText = `${count} Baris Metode`;
        document.getElementById('splitSummaryTotal').innerText = formatRupiah(currentGrandTotal);
        document.getElementById('splitSummaryPaid').innerText = formatRupiah(totalPaid);

        const statusLabel = document.getElementById('splitSummaryStatusLabel');
        const diffDisplay = document.getElementById('splitSummaryDiff');

        if (totalPaid < currentGrandTotal) {
            const diff = currentGrandTotal - totalPaid;
            statusLabel.innerText = 'Sisa Kurang';
            diffDisplay.className = 'fw-bold fs-7 text-danger';
            diffDisplay.innerText = formatRupiah(diff);
        } else if (totalPaid === currentGrandTotal) {
            statusLabel.innerText = 'Status';
            diffDisplay.className = 'fw-bold fs-7 text-success';
            diffDisplay.innerText = 'LUNAS (Pas)';
        } else {
            const change = totalPaid - currentGrandTotal;
            statusLabel.innerText = 'Kembalian';
            diffDisplay.className = 'fw-bold fs-7 text-success';
            diffDisplay.innerText = formatRupiah(change);
        }
    }

    function openPaymentModal() {
        if (!hasActiveShift) {
            promptOpenShift();
            return;
        }
        if (cart.length === 0) return;
        document.getElementById('modalGrandTotal').innerText = formatRupiah(currentGrandTotal);
        document.getElementById('paidAmountInput').value = currentGrandTotal;
        togglePayMethod();
        calculateChange();

        // If in split mode, recalculate split values
        if (currentPaymentMode === 'split') {
            const container = document.getElementById('splitPaymentRowsContainer');
            if (container.children.length === 0) {
                initDefaultSplitRows();
            } else {
                calculateSplitPaymentTotals();
            }
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('paymentModal')).show();
    }

    function togglePayMethod() {
        const isCash = document.getElementById('payCash').checked;
        const isTransfer = document.getElementById('payTransfer').checked;
        const isQris = document.getElementById('payQris').checked;
        const isDeposit = document.getElementById('payDeposit').checked;
        const cashSection = document.getElementById('cashPaymentSection');
        const depositSection = document.getElementById('depositInfoSection');
        const accountGroup = document.getElementById('accountSelectGroup');
        const accountSelect = document.getElementById('modalAccountSelect');

        if (isDeposit) {
            cashSection.style.display = 'none';
            accountGroup.style.display = 'none';
            depositSection.classList.remove('d-none');
            
            const custSelect = document.getElementById('posCustomerSelect');
            const opt = custSelect.options[custSelect.selectedIndex];
            const deposit = parseFloat(opt.getAttribute('data-deposit') || 0);
            
            document.getElementById('modalDepositCustomerName').innerText = opt.innerText;
            document.getElementById('modalDepositBalanceText').innerText = `Saldo Deposit Tersedia: ${formatRupiah(deposit)} (Total Tagihan: ${formatRupiah(currentGrandTotal)})`;
        } else if (isCash) {
            cashSection.style.display = 'block';
            accountGroup.style.display = 'block';
            depositSection.classList.add('d-none');
            calculateChange();
            let found = false;
            if (activeShiftCashAccountId) {
                for (let i = 0; i < accountSelect.options.length; i++) {
                    if (accountSelect.options[i].value == activeShiftCashAccountId) {
                        accountSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
            }
            if (!found) {
                for (let i = 0; i < accountSelect.options.length; i++) {
                    if (accountSelect.options[i].getAttribute('data-type') === 'cash') {
                        accountSelect.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
            }
            if (!found && accountSelect.options.length > 0) {
                accountSelect.selectedIndex = 0;
            }
        } else {
            cashSection.style.display = 'none';
            accountGroup.style.display = 'block';
            depositSection.classList.add('d-none');
            document.getElementById('paidAmountInput').value = currentGrandTotal;
            let found = false;
            for (let i = 0; i < accountSelect.options.length; i++) {
                if (accountSelect.options[i].getAttribute('data-type') === 'bank') {
                    accountSelect.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found && accountSelect.options.length > 0) {
                accountSelect.selectedIndex = 0;
            }
        }
    }

    function setQuickCash(amount) {
        if (amount === 'exact') {
            document.getElementById('paidAmountInput').value = currentGrandTotal;
        } else {
            document.getElementById('paidAmountInput').value = amount;
        }
        calculateChange();
    }

    function calculateChange() {
        const paid = parseFloat(document.getElementById('paidAmountInput').value) || 0;
        const change = Math.max(0, paid - currentGrandTotal);
        document.getElementById('changeAmountDisplay').innerText = formatRupiah(change);
    }

    function processCheckout() {
        const warehouseId = document.getElementById('posWarehouseSelect').value;
        const customerId = document.getElementById('posCustomerSelect').value;
        const discountAmount = parseFloat(document.getElementById('cartDiscountInput').value) || 0;

        let payloadPayments = [];
        let singlePaymentMethod = 'cash';
        let singleAccountId = null;
        let singlePaidAmount = 0;

        if (currentPaymentMode === 'single') {
            singlePaymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            singleAccountId = document.getElementById('modalAccountSelect').value;
            singlePaidAmount = parseFloat(document.getElementById('paidAmountInput').value) || 0;

            if (singlePaymentMethod === 'cash' && singlePaidAmount < currentGrandTotal) {
                alert('Jumlah uang yang dibayarkan kurang dari Total Tagihan!');
                return;
            }

            if (singlePaymentMethod === 'deposit') {
                const custSelect = document.getElementById('posCustomerSelect');
                const opt = custSelect.options[custSelect.selectedIndex];
                const deposit = parseFloat(opt.getAttribute('data-deposit') || 0);
                if (deposit < currentGrandTotal) {
                    alert(`Saldo deposit (${formatRupiah(deposit)}) tidak mencukupi untuk total transaksi (${formatRupiah(currentGrandTotal)}).`);
                    return;
                }
                singlePaidAmount = currentGrandTotal;
            }

            payloadPayments.push({
                payment_method: singlePaymentMethod,
                account_id: singlePaymentMethod === 'deposit' ? null : singleAccountId,
                amount: singlePaidAmount,
                reference_no: null
            });
        } else {
            // Split payment mode
            const rows = document.querySelectorAll('#splitPaymentRowsContainer .split-payment-row');
            let splitTotalPaid = 0;
            let splitDepositTotal = 0;

            rows.forEach(row => {
                const method = row.querySelector('.split-method-select').value;
                const accId = row.querySelector('.split-account-select').value;
                const amt = parseFloat(row.querySelector('.split-amount-input').value) || 0;
                const ref = row.querySelector('.split-ref-input').value || '';

                if (amt > 0) {
                    splitTotalPaid += amt;
                    if (method === 'deposit') splitDepositTotal += amt;

                    payloadPayments.push({
                        payment_method: method,
                        account_id: method === 'deposit' ? null : accId,
                        amount: amt,
                        reference_no: ref
                    });
                }
            });

            if (splitTotalPaid < currentGrandTotal) {
                alert(`Total pembayaran split (${formatRupiah(splitTotalPaid)}) kurang dari total tagihan (${formatRupiah(currentGrandTotal)})!`);
                return;
            }

            if (splitDepositTotal > 0) {
                const custSelect = document.getElementById('posCustomerSelect');
                const opt = custSelect.options[custSelect.selectedIndex];
                const deposit = parseFloat(opt.getAttribute('data-deposit') || 0);
                if (deposit < splitDepositTotal) {
                    alert(`Saldo deposit pelanggan (${formatRupiah(deposit)}) tidak mencukupi untuk pembayaran deposit split (${formatRupiah(splitDepositTotal)}).`);
                    return;
                }
            }
        }

        // Validate margin limit
        for (let item of cart) {
            if (!item.isService && !item.isBundle && item.minPrice > 0 && item.price < item.minPrice) {
                alert(`Pencegahan Margin: Harga jual '${item.name}' (Rp ${item.price.toLocaleString('id-ID')}) berada di bawah batas minimum margin ${item.margin}% (Minimal: Rp ${Math.round(item.minPrice).toLocaleString('id-ID')}).`);
                return;
            }
        }

        const btnSubmit = document.getElementById('btnSubmitCheckout');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

        const payload = {
            warehouse_id: warehouseId,
            customer_id: customerId,
            payment_method: currentPaymentMode === 'single' ? singlePaymentMethod : 'split',
            account_id: currentPaymentMode === 'single' ? singleAccountId : null,
            paid_amount: currentPaymentMode === 'single' ? singlePaidAmount : payloadPayments.reduce((s, p) => s + p.amount, 0),
            discount_amount: discountAmount,
            tax_amount: currentTaxAmount,
            payments: payloadPayments,
            items: cart.map(i => ({
                item_id: i.id,
                quantity: i.qty,
                koli: i.koli || null,
                unit_price: i.price
            })),
            _token: '{{ csrf_token() }}'
        };

        fetch('{{ route('pos.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(res => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Selesaikan Transaksi';

            if (res.success) {
                // Close payment modal
                bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();

                // Open receipt modal
                document.getElementById('receiptInvoiceNo').innerText = res.invoice_number;
                document.getElementById('receiptFrame').src = res.receipt_url;
                new bootstrap.Modal(document.getElementById('receiptModal')).show();

                // Reset cart & split container
                cart = [];
                document.getElementById('cartDiscountInput').value = 0;
                document.getElementById('cartTaxRateInput').value = 0;
                document.getElementById('splitPaymentRowsContainer').innerHTML = '';
                renderCart();
            } else {
                alert('Gagal: ' + (res.message || 'Terjadi kesalahan'));
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Selesaikan Transaksi';
            alert('Terjadi kesalahan jaringan atau server.');
            console.error(err);
        });
    }

    function printReceiptFrame() {
        const frame = document.getElementById('receiptFrame');
        if (frame && frame.contentWindow) {
            frame.contentWindow.print();
        }
    }

    // Live search & category filters
    document.getElementById('posSearchInput').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        document.querySelectorAll('.product-item-col').forEach(col => {
            const name = col.getAttribute('data-name');
            const code = col.getAttribute('data-code');
            const barcode = col.getAttribute('data-barcode');
            if (name.includes(query) || code.includes(query) || barcode.includes(query)) {
                col.style.display = '';
            } else {
                col.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.category-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.category-filter-btn').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-light-primary');
            });
            this.classList.remove('btn-light-primary');
            this.classList.add('btn-primary');

            const catId = this.getAttribute('data-category-id');
            document.querySelectorAll('.product-item-col').forEach(col => {
                const itemCat = col.getAttribute('data-category');
                if (!catId || itemCat === catId) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            });
        });
    });

    function handlePosNewItem(item) {
        addToCart(item.id, item.name, item.code, item.selling_price, 999, item.unit_symbol || 'pcs', item.item_type === 'service', item.selling_price, 0, item.item_type === 'bundle', Boolean(item.allow_change_price));
    }

    // Keyboard shortcut F4 for payment
    document.addEventListener('keydown', function(e) {
        if (e.key === 'F4') {
            e.preventDefault();
            if (cart.length > 0) openPaymentModal();
        }
    });
</script>
@endsection
