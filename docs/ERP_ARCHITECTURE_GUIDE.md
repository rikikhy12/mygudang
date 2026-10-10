# MYCRUD ERP Enterprise Suite - Panduan Arsitektur & Operasional Sistem

Dokumen komprehensif ini merangkum seluruh arsitektur modul, basis data, alur bisnis, aturan validasi, dan pedoman antarmuka pengguna (UI) dari sistem **MYCRUD ERP**.

---

## 1. Ikhtisar Arsitektur Sistem

Sistem ERP ini dibangun dengan fondasi:
- **Framework**: Laravel 11.x (PHP 8.3+)
- **Database**: MySQL / MariaDB (Multi-Branch, Double-Entry General Ledger, Multi-Warehouse)
- **Tema & UI**: Metronic 8 Theme (Bootstrap 5, Obsidian Dark Sidebar, Vanilla CSS + DataTables)
- **Pencarian Cepat**: Windows Spotlight Command Palette (`Ctrl + K`) di Topbar Header

---

## 2. Modul-Modul Utama & Alur Bisnis

### A. Master Data & Inventori
1. **Master Barang & Jasa (`/items`)**:
   - Tipe: Barang Fisik (Track Inventory) & Jasa/Layanan (Non-Inventory).
   - Pengunggahan Foto Produk: Disimpan di `public/uploads/items` & `storage/app/public/items` dengan proteksi fallback route `/storage/{path}` agar terhindar dari *403 Forbidden*.
   - Margin Profit Minimal (`effective_min_margin_percent`): Mencegah penjualan di bawah batas harga pokok/margin.
   - Kartu Stok Multi-Gudang & Pelacakan Batch/Lot.
2. **Kategori, Merek & Satuan Unit**: Pengelompokan hierarki inventori.
3. **Multi-Cabang (`/branches`) & Gudang (`/warehouses`)**: Dukungan multi-outlet dengan unit pusat (HQ) dan cabang operasional.
4. **Kontak & Rekanan (`/contacts`)**: Database pelanggan (*Customer*) dan pemasok (*Supplier*) dengan batas kredit (*Credit Limit*) dan TOP (*Terms of Payment*).

---

### B. Modul Pengadaan (Procurement / Pembelian)
1. **Permintaan Pembelian / PR (`/procurement/pr`)**: Pengajuan kebutuhan barang dari divisi/cabang dengan alur persetujuan (*Approval Workflow*).
2. **Pesanan Pembelian / PO (`/procurement/po`)**: Pembuatan order resmi ke supplier dengan kalkulasi PPN/Diskon.
3. **Penerimaan Barang / GRN (`/procurement/grn`)**: Penerimaan fisik ke gudang tertentu yang langsung menambah kuantitas stok.
4. **Tagihan Pemasok / AP Invoices (`/procurement/invoices`)**:
   - Shortcut Cepat: `Alt + A`.
   - Menghasilkan jurnal hutang usaha: Debet Persediaan/Biaya, Kredit Hutang Usaha (AP).

---

### C. Modul Penjualan (Sales) & Kasir (POS)
1. **Point of Sale / Kasir POS (`/pos`)**:
   - Katalog produk interaktif lengkap dengan foto thumbnail, badge stok, dan filter kategori.
   - Dukungan scan barcode, multi-pembayaran (Tunai, Transfer, QRIS, Split Payment), dan cetak struk thermal 58mm/80mm.
2. **Pesanan Penjualan / SO (`/sales/so`)**: Pencatatan order pelanggan sebelum proses pengiriman.
3. **Surat Jalan / DO (`/sales/do`)**: Pengeluaran fisik barang dari gudang menuju pelanggan.
4. **Faktur Penjualan / AR Invoices (`/sales/invoices`)**:
   - Shortcut Cepat: `Alt + R`.
   - Otomatis membukukan jurnal piutang dan pendapatan penjualan.
5. **Laporan Analisis Penjualan (`/sales/reports/sales-summary`)**:
   - Kontrol radio button (*Fokus Tampilan*): *Semua Rekap*, *Per Cabang*, *Per Kategori*, *Top 10 Produk*, *Rincian Faktur*.

---

### D. Manufaktur & Produksi (Manufacturing)
1. **Bill of Materials / BOM (`/manufacturing/boms`)**: Formula komposisi bahan baku (*Raw Materials*) yang dibutuhkan untuk memproduksi 1 unit *Finished Good*.
2. **Routing Tahapan Produksi (`/manufacturing/routings`)**: Urutan stasiun kerja dan langkah operasional (Step A &rarr; Step B &rarr; Step C &rarr; Step D &rarr; Step E).
3. **Surat Perintah Kerja / Work Orders (`/manufacturing/work-orders`)**:
   - Visual step pipeline indikator tahap berjalan.
   - Eksekusi batch manufaktur: Otomatis memotong stok bahan baku dari Gudang Bahan Baku, menambah stok barang jadi ke Gudang Barang Jadi, dan membentuk jurnal akuntansi HPP Produksi.

---

### E. Keuangan, Kas & Akuntansi (Finance & General Ledger)
1. **Kas Masuk (`/finance/cash-in`) & Kas Keluar (`/finance/cash-out`)**: Transaksi operasional harian.
2. **Deposit Uang Muka (`/deposits`)**: Pembayaran uang muka pelanggan / supplier yang dapat dialokasikan ke faktur berjalan.
3. **Jurnal Umum (`/finance/journals`)**: Jurnal manual debet-kredit memorial berimbang (*balanced*).
4. **Laporan Keuangan Standar SAK**:
   - **Buku Besar (General Ledger)**: Mutasi per akun COA.
   - **Neraca Saldo (Trial Balance)**: Verifikasi saldo debit = kredit.
   - **Laporan Laba Rugi (Profit & Loss)**: Analisis pendapatan vs beban.
   - **Neraca Keuangan (Balance Sheet)**: Posisi Aset, Kewajiban, dan Ekuitas.

---

## 3. Fitur Navigasi & Shortcut Global

| Shortcut | Fungsi |
|---|---|
| `Ctrl + K` / `Cmd + K` | Membuka Windows Spotlight Command Palette untuk mencari menu/transaksi secara instan |
| `Alt + A` | Navigasi langsung ke Daftar Tagihan Pemasok (AP Invoices) |
| `Alt + R` | Navigasi langsung ke Daftar Faktur Penjualan (AR Invoices) |
| `Esc` | Menutup Modal Spotlight Search atau jendela popup aktif |
| `&uarr;` / `&darr;` + `Enter` | Navigasi keyboard pada hasil pencarian Spotlight |

---

## 4. Struktur Database & Konvensi

- Seluruh tabel transaksi keuangan memicu pencatatan double-entry ke tabel `journals` dan `journal_lines`.
- Multi-branch dikontrol melalui foreign key `branch_id` dan session branch active.
- Foto barang disimpan pada kolom `items.image` dengan resolusi responsif.
