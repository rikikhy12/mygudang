<!-- Windows 10 / 11 Fluent Search & Start Launcher Modal (Ctrl + K) -->
<div class="modal fade" id="spotlightSearchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 740px;">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden win-launcher-modal" style="background: rgba(32, 32, 36, 0.95); backdrop-filter: blur(28px) saturate(180%); -webkit-backdrop-filter: blur(28px) saturate(180%); border: 1px solid rgba(255, 255, 255, 0.12) !important; color: #f1f5f9;">
            
            <!-- Windows Search Input Header -->
            <div class="p-4 pb-3 border-bottom border-secondary border-opacity-25" style="background: rgba(255, 255, 255, 0.02);">
                <div class="position-relative d-flex align-items-center">
                    <div class="position-absolute start-0 ms-3 text-primary d-flex align-items-center pointer-events-none">
                        <i class="bi bi-search fs-5 text-info"></i>
                    </div>
                    <input type="text" id="spotlightSearchInput" 
                        class="form-control form-control-solid ps-11 pe-12 py-3 fs-6 fw-semibold text-white rounded-3 shadow-inner" 
                        placeholder="Ketik untuk mencari aplikasi, transaksi, modul, atau dokumen..." 
                        autocomplete="off" 
                        style="background: rgba(0, 0, 0, 0.45); border: 1px solid rgba(255, 255, 255, 0.15); border-bottom: 2px solid #0ea5e9; color: #ffffff !important; outline: none !important;">
                    <div class="position-absolute end-0 me-3">
                        <kbd class="badge bg-dark text-gray-400 border border-secondary border-opacity-50 fs-9 px-2 py-1">ESC</kbd>
                    </div>
                </div>
            </div>

            <!-- Launcher Body -->
            <div class="modal-body p-4 pt-3" style="max-height: 520px; overflow-y: auto;" id="spotlightBody">
                
                <!-- DEFAULT WINDOWS START VIEW (Shown when search is empty) -->
                <div id="winDefaultView">
                    
                    <!-- Pinned Apps Section -->
                    <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                        <span class="fs-7 fw-bolder text-white text-uppercase" style="letter-spacing: 0.05em;">
                            <i class="bi bi-pin-angle-fill text-warning me-1"></i> Disematkan (Pinned)
                        </span>
                        <span class="badge bg-dark text-muted border border-secondary border-opacity-25 fs-9">Aplikasi Utama</span>
                    </div>

                    <div class="row g-2 mb-4">
                        <!-- App: POS Kasir -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('pos.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="POS Kasir Retail">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #107c41 0%, #0b5a2f 100%);">
                                    <i class="bi bi-calculator-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">POS Kasir</span>
                            </a>
                        </div>

                        <!-- App: Faktur AR -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('sales.invoices.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Faktur Penjualan (AR)">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #0078d4 0%, #004e8c 100%);">
                                    <i class="bi bi-file-earmark-ruled-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Faktur AR</span>
                            </a>
                        </div>

                        <!-- App: Tagihan AP -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('procurement.invoices.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Tagihan Pemasok (AP)">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #d83b01 0%, #a80000 100%);">
                                    <i class="bi bi-receipt-cutoff fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Tagihan AP</span>
                            </a>
                        </div>

                        <!-- App: Sales Order -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('sales.so.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Sales Order (SO)">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #008272 0%, #005a54 100%);">
                                    <i class="bi bi-cart-check-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Sales Order</span>
                            </a>
                        </div>

                        <!-- App: Purchase Order -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('procurement.po.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Purchase Order (PO)">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #5c2d91 0%, #3b1766 100%);">
                                    <i class="bi bi-bag-check-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Order Beli</span>
                            </a>
                        </div>

                        <!-- App: Master Barang -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('items.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Master Barang & Jasa">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #038387 0%, #005e60 100%);">
                                    <i class="bi bi-box-seam-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Master Item</span>
                            </a>
                        </div>

                        <!-- App: BOM Manufaktur -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('manufacturing.boms.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Bill of Materials">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #ff8c00 0%, #d83b01 100%);">
                                    <i class="bi bi-diagram-3-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">BOM Resep</span>
                            </a>
                        </div>

                        <!-- App: Work Order SPK -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('manufacturing.work_orders.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Perintah Kerja (SPK)">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #744da9 0%, #4f2d7f 100%);">
                                    <i class="bi bi-gear-wide-connected fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">SPK Produksi</span>
                            </a>
                        </div>

                        <!-- App: Deposit Pelanggan -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('finance.deposits.customer') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Deposit Pelanggan">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #107c41 0%, #004b1c 100%);">
                                    <i class="bi bi-wallet2 fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Deposit Cust</span>
                            </a>
                        </div>

                        <!-- App: Jurnal Umum -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('accounting.journals.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Jurnal Umum">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #323130 0%, #201f1e 100%); border: 1px solid rgba(255,255,255,0.15);">
                                    <i class="bi bi-book-half fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Jurnal Umum</span>
                            </a>
                        </div>

                        <!-- App: Laporan Sales -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('sales.reports.sales-summary') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Laporan Analisis Penjualan">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #2b579a 0%, #124078 100%);">
                                    <i class="bi bi-bar-chart-fill fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Laporan Sales</span>
                            </a>
                        </div>

                        <!-- App: Master Gudang -->
                        <div class="col-3 col-sm-2 text-center">
                            <a href="{{ route('warehouses.index') }}" class="win-app-tile d-flex flex-column align-items-center p-2 rounded-3 text-decoration-none" title="Master Gudang">
                                <div class="win-app-icon shadow-sm mb-1 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #498205 0%, #294d03 100%);">
                                    <i class="bi bi-building fs-3 text-white"></i>
                                </div>
                                <span class="win-app-label text-truncate w-100">Gudang</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recommended / Quick Documents Section -->
                    <div class="d-flex align-items-center justify-content-between mb-2 px-1 pt-2 border-top border-secondary border-opacity-25">
                        <span class="fs-7 fw-bolder text-white text-uppercase" style="letter-spacing: 0.05em;">
                            <i class="bi bi-clock-history text-info me-1"></i> Rekomendasi & Akses Cepat
                        </span>
                        <span class="text-muted fs-8">Pintas Dokumen</span>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <a href="{{ route('finance.deposits.create', ['type' => 'customer']) }}" class="win-rec-item d-flex align-items-center p-2 rounded-3 text-decoration-none">
                                <div class="win-rec-icon bg-success bg-opacity-25 text-success me-3 rounded-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-wallet-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="win-rec-title text-truncate">Input Mutasi Deposit Pelanggan</div>
                                    <div class="win-rec-sub text-truncate">Top up saldo / DP uang muka</div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="{{ route('finance.deposits.supplier') }}" class="win-rec-item d-flex align-items-center p-2 rounded-3 text-decoration-none">
                                <div class="win-rec-icon bg-danger bg-opacity-25 text-danger me-3 rounded-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-truck fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="win-rec-title text-truncate">Kelola Deposit Pemasok</div>
                                    <div class="win-rec-sub text-truncate">Uang muka pembelian supplier</div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="{{ route('items.create') }}" class="win-rec-item d-flex align-items-center p-2 rounded-3 text-decoration-none">
                                <div class="win-rec-icon bg-info bg-opacity-25 text-info me-3 rounded-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-box-seam fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="win-rec-title text-truncate">Tambah Barang & Upload Foto</div>
                                    <div class="win-rec-sub text-truncate">Daftarkan produk baru ke katalog</div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6">
                            <a href="{{ route('inventory.movements') }}" class="win-rec-item d-flex align-items-center p-2 rounded-3 text-decoration-none">
                                <div class="win-rec-icon bg-warning bg-opacity-25 text-warning me-3 rounded-2 d-flex align-items-center justify-content-center">
                                    <i class="bi bi-arrow-left-right fs-5"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="win-rec-title text-truncate">Kartu Stok & Mutasi Gudang</div>
                                    <div class="win-rec-sub text-truncate">Audit trail pergerakan kuantitas</div>
                                </div>
                            </a>
                        </div>
                    </div>

                </div>

                <!-- LIVE SEARCH RESULTS VIEW (Shown when search input is typed) -->
                <div id="winSearchResults" class="d-none">
                    
                    <div class="list-group list-group-flush gap-1" id="spotlightItemsList">
                        
                        <!-- Transaksi & Kasir -->
                        <a href="{{ route('pos.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="pos kasir retail penjualan toko kasir barcode point of sale">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #107c41;">
                                    <i class="bi bi-calculator-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Point of Sale (POS Kasir)</div>
                                    <div class="text-muted fs-8">Layanan kasir penjualan langsung, multi-bayar & cetak struk</div>
                                </div>
                            </div>
                            <span class="badge bg-success bg-opacity-25 text-success fw-bold fs-9 border border-success border-opacity-50">POS</span>
                        </a>

                        <a href="{{ route('sales.so.create') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="so sales order buat pesanan jual tambah sales order">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #0078d4;">
                                    <i class="bi bi-cart-plus-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Buat Sales Order (SO)</div>
                                    <div class="text-muted fs-8">Pesanan penjualan baru dari pelanggan</div>
                                </div>
                            </div>
                            <span class="badge bg-primary bg-opacity-25 text-info fw-bold fs-9">SO Baru</span>
                        </a>

                        <a href="{{ route('procurement.po.create') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="po purchase order buat pesanan beli tambah order beli supplier">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #5c2d91;">
                                    <i class="bi bi-bag-plus-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Buat Purchase Order (PO)</div>
                                    <div class="text-muted fs-8">Pemesanan barang ke supplier / vendor</div>
                                </div>
                            </div>
                            <span class="badge bg-purple bg-opacity-25 text-warning fw-bold fs-9">PO Baru</span>
                        </a>

                        <a href="{{ route('procurement.invoices.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="tagihan ap invoices hutang pembayaran supplier faktur beli alt a">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #d83b01;">
                                    <i class="bi bi-receipt-cutoff fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Tagihan Pemasok (AP Invoices)</div>
                                    <div class="text-muted fs-8">Kelola tagihan hutang usaha & pembayaran pemasok</div>
                                </div>
                            </div>
                            <kbd class="badge bg-dark text-muted border border-secondary fs-9 fw-bold">Alt + A</kbd>
                        </a>

                        <a href="{{ route('sales.invoices.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="faktur ar invoices piutang tagihan pelanggan faktur jual alt r">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #008272;">
                                    <i class="bi bi-file-earmark-ruled-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Faktur Penjualan (AR Invoices)</div>
                                    <div class="text-muted fs-8">Penerbitan faktur piutang & pelunasan pelanggan</div>
                                </div>
                            </div>
                            <kbd class="badge bg-dark text-muted border border-secondary fs-9 fw-bold">Alt + R</kbd>
                        </a>

                        <!-- Deposit & Keuangan -->
                        <a href="{{ route('finance.deposits.customer') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="deposit customer uang muka pelanggan saldo titipan top up saldo">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #107c41;">
                                    <i class="bi bi-wallet2 fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Deposit Pelanggan (Customer)</div>
                                    <div class="text-muted fs-8">Uang muka titipan & saldo deposit pelanggan</div>
                                </div>
                            </div>
                            <span class="badge bg-success bg-opacity-25 text-success fs-9">Deposit Cust</span>
                        </a>

                        <a href="{{ route('finance.deposits.supplier') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="deposit supplier uang muka pemasok vendor panjar beli">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #d83b01;">
                                    <i class="bi bi-wallet-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Deposit Pemasok (Supplier)</div>
                                    <div class="text-muted fs-8">Uang muka pembelian & panjar ke pemasok</div>
                                </div>
                            </div>
                            <span class="badge bg-danger bg-opacity-25 text-danger fs-9">Deposit Supplier</span>
                        </a>

                        <!-- Master Data -->
                        <a href="{{ route('items.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="master barang produk items sku stok jasa inventori foto">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #038387;">
                                    <i class="bi bi-box-seam-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Master Barang & Jasa</div>
                                    <div class="text-muted fs-8">Katalog produk, harga, foto produk & saldo stok</div>
                                </div>
                            </div>
                            <span class="badge bg-info bg-opacity-25 text-info fs-9">Master</span>
                        </a>

                        <a href="{{ route('warehouses.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="gudang warehouse lokasi simpan rak fisik">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #498205;">
                                    <i class="bi bi-building fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Master Gudang</div>
                                    <div class="text-muted fs-8">Lokasi penyimpanan inventori fisik</div>
                                </div>
                            </div>
                            <span class="badge bg-secondary text-white fs-9">Gudang</span>
                        </a>

                        <a href="{{ route('inventory.movements') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="mutasi stok kartu stok inventory movement barang masuk keluar">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #2b579a;">
                                    <i class="bi bi-arrow-left-right fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Mutasi Kartu Stok</div>
                                    <div class="text-muted fs-8">Riwayat audit pergerakan keluar-masuk stok barang</div>
                                </div>
                            </div>
                            <span class="badge bg-primary bg-opacity-25 text-primary fs-9">Mutasi Stok</span>
                        </a>

                        <!-- Manufaktur -->
                        <a href="{{ route('manufacturing.boms.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="bom bill of materials resep formula bahan baku struktur produk">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #ff8c00;">
                                    <i class="bi bi-diagram-3-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Bill of Materials (BOM)</div>
                                    <div class="text-muted fs-8">Formula bahan baku & estimasi biaya produksi</div>
                                </div>
                            </div>
                            <span class="badge bg-warning bg-opacity-25 text-warning fs-9">BOM</span>
                        </a>

                        <a href="{{ route('manufacturing.work_orders.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="spk work order wo surat perintah kerja produksi eksekusi batch">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #744da9;">
                                    <i class="bi bi-gear-wide-connected fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Perintah Kerja (Work Orders / SPK)</div>
                                    <div class="text-muted fs-8">Eksekusi manufaktur, routing batch & output produk</div>
                                </div>
                            </div>
                            <span class="badge bg-purple bg-opacity-25 text-warning fs-9">SPK / WO</span>
                        </a>

                        <!-- Akuntansi & Laporan -->
                        <a href="{{ route('accounting.journals.index') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="jurnal umum general journal memorial ayat jurnal debet kredit">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #323130; border: 1px solid rgba(255,255,255,0.2);">
                                    <i class="bi bi-book-half fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Jurnal Umum (General Journal)</div>
                                    <div class="text-muted fs-8">Pencatatan transaksi memorial debet-kredit</div>
                                </div>
                            </div>
                            <span class="badge bg-dark text-muted fs-9">Jurnal</span>
                        </a>

                        <a href="{{ route('accounting.reports.general-ledger') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="buku besar general ledger mutasi akun histori saldo">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #0078d4;">
                                    <i class="bi bi-journal-text fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Buku Besar (General Ledger)</div>
                                    <div class="text-muted fs-8">Rincian mutasi debet/kredit per akun COA</div>
                                </div>
                            </div>
                            <span class="badge bg-info bg-opacity-25 text-info fs-9">Ledger</span>
                        </a>

                        <a href="{{ route('sales.reports.sales-summary') }}" class="spotlight-item list-group-item d-flex align-items-center justify-content-between p-3 rounded-3 border-0" data-keywords="laporan penjualan sales report omset grafik rekap faktur rincian cabang kategori">
                            <div class="d-flex align-items-center gap-3">
                                <div class="w-35px h-35px rounded-2 d-flex align-items-center justify-content-center" style="background: #2b579a;">
                                    <i class="bi bi-bar-chart-line-fill fs-5 text-white"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">Laporan Analisis Penjualan</div>
                                    <div class="text-muted fs-8">Ringkasan omset per cabang, kategori & rincian faktur</div>
                                </div>
                            </div>
                            <span class="badge bg-primary bg-opacity-25 text-primary fs-9">Laporan Sales</span>
                        </a>
                    </div>

                    <!-- Empty Search State -->
                    <div id="spotlightEmptyState" class="text-center py-5 d-none">
                        <i class="bi bi-search fs-1 text-muted opacity-50 mb-3 d-block"></i>
                        <div class="text-white fw-bold fs-6">Tidak menemukan hasil yang cocok</div>
                        <div class="text-muted fs-8">Coba kata kunci lain seperti <code>POS</code>, <code>Deposit</code>, <code>Gudang</code>, <code>PO</code>, <code>Faktur</code></div>
                    </div>

                </div>

            </div>

            <!-- Windows Taskbar / User Footer -->
            <div class="modal-footer px-4 py-3 border-top border-secondary border-opacity-25 d-flex align-items-center justify-content-between" style="background: rgba(20, 20, 24, 0.98);">
                <div class="d-flex align-items-center gap-3">
                    <div class="w-35px h-35px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bolder shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold text-white fs-7 lh-1">{{ auth()->user()->name ?? 'Administrator' }}</div>
                        <span class="text-muted fs-9">{{ auth()->user()->roles->pluck('display_name')->first() ?? 'Staff ERP' }}</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-icon btn-sm btn-light-danger rounded-circle" title="Keluar / Shut Down">
                            <i class="bi bi-power fs-5"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Windows Fluent Launcher Dark Styling */
    .win-launcher-modal {
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.1) !important;
    }
    
    .win-app-tile {
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    .win-app-tile:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-2px);
    }
    .win-app-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        transition: transform 0.15s ease;
    }
    .win-app-tile:hover .win-app-icon {
        transform: scale(1.06);
    }
    .win-app-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #e2e8f0;
        margin-top: 4px;
        display: block;
    }

    .win-rec-item {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .win-rec-item:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.15);
        transform: translateX(3px);
    }
    .win-rec-icon {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
    }
    .win-rec-title {
        font-size: 0.82rem;
        font-weight: 600;
        color: #ffffff;
    }
    .win-rec-sub {
        font-size: 0.72rem;
        color: #94a3b8;
    }

    /* Live Search List Item */
    .spotlight-item {
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.06) !important;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }
    .spotlight-item:hover, .spotlight-item.active-item {
        background: rgba(255, 255, 255, 0.12) !important;
        border-color: rgba(14, 165, 233, 0.5) !important;
        transform: translateX(4px);
    }
    .spotlight-item.active-item {
        border-left: 4px solid #0ea5e9 !important;
    }
</style>

<script>
    let spotlightModal = null;
    let selectedSpotlightIndex = -1;

    function openSpotlightSearch() {
        const modalEl = document.getElementById('spotlightSearchModal');
        if (!modalEl) return;
        
        if (!spotlightModal) {
            spotlightModal = new bootstrap.Modal(modalEl);
        }
        
        spotlightModal.show();
        setTimeout(() => {
            const inputEl = document.getElementById('spotlightSearchInput');
            if (inputEl) {
                inputEl.value = '';
                inputEl.focus();
                handleLauncherSearch('');
            }
        }, 150);
    }

    function handleLauncherSearch(query) {
        query = (query || '').toLowerCase().trim();
        const defaultView = document.getElementById('winDefaultView');
        const searchResults = document.getElementById('winSearchResults');
        const emptyEl = document.getElementById('spotlightEmptyState');
        const items = document.querySelectorAll('.spotlight-item');

        if (!query) {
            if (defaultView) defaultView.classList.remove('d-none');
            if (searchResults) searchResults.classList.add('d-none');
            return;
        }

        if (defaultView) defaultView.classList.add('d-none');
        if (searchResults) searchResults.classList.remove('d-none');

        let visibleCount = 0;
        items.forEach(item => {
            const text = (item.textContent + ' ' + (item.getAttribute('data-keywords') || '')).toLowerCase();
            if (text.includes(query)) {
                item.classList.remove('d-none');
                item.classList.add('d-flex');
                visibleCount++;
            } else {
                item.classList.add('d-none');
                item.classList.remove('d-flex');
            }
        });

        if (emptyEl) {
            if (visibleCount === 0) {
                emptyEl.classList.remove('d-none');
            } else {
                emptyEl.classList.add('d-none');
            }
        }

        selectedSpotlightIndex = -1;
        updateActiveSpotlightItem();
    }

    function updateActiveSpotlightItem() {
        const visibleItems = Array.from(document.querySelectorAll('#spotlightItemsList .spotlight-item:not(.d-none)'));
        visibleItems.forEach((item, index) => {
            if (index === selectedSpotlightIndex) {
                item.classList.add('active-item');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('active-item');
            }
        });
    }

    // Global Key Listener: Ctrl+K or Cmd+K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openSpotlightSearch();
        }
    });

    // Modal Search Input Keyboard Navigation
    document.addEventListener('DOMContentLoaded', function() {
        const inputEl = document.getElementById('spotlightSearchInput');
        if (inputEl) {
            inputEl.addEventListener('input', function() {
                handleLauncherSearch(this.value);
            });

            inputEl.addEventListener('keydown', function(e) {
                const searchResults = document.getElementById('winSearchResults');
                if (searchResults && searchResults.classList.contains('d-none')) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                    return;
                }

                const visibleItems = Array.from(document.querySelectorAll('#spotlightItemsList .spotlight-item:not(.d-none)'));
                if (visibleItems.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedSpotlightIndex = (selectedSpotlightIndex + 1) % visibleItems.length;
                    updateActiveSpotlightItem();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedSpotlightIndex = (selectedSpotlightIndex - 1 + visibleItems.length) % visibleItems.length;
                    updateActiveSpotlightItem();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedSpotlightIndex >= 0 && selectedSpotlightIndex < visibleItems.length) {
                        visibleItems[selectedSpotlightIndex].click();
                    } else if (visibleItems.length > 0) {
                        visibleItems[0].click();
                    }
                }
            });
        }
    });
</script>
