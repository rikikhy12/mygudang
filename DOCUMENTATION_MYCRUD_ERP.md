# PANDUAN DOKUMENTASI LENGKAP SISTEM ERP ENTERPRISE
## PT MYCRUD TRANSFORMASI DIGITAL (MYCRUD ERP)

Dokumentasi ini menyajikan arsitektur, panduan operasional, konfigurasi akuntansi, dan alur kerja (workflow) lengkap sistem ERP dari awal hingga akhir.

---

## DAFTAR ISI
1. [Arsitektur & Ikhtisar Sistem](#1-arsitektur--ikhtisar-sistem)
2. [Master Data & Pengaturan Awal](#2-master-data--pengaturan-awal)
   - [2.1 Chart of Accounts (COA) & GL Mapping](#21-chart-of-accounts-coa--gl-mapping)
   - [2.2 Master Barang, Jasa & Multi-UOM](#22-master-barang-jasa--multi-uom)
   - [2.3 Multi-Cabang & Multi-Gudang](#23-multi-cabang--multi-gudang)
   - [2.4 Kontak Rekanan & Limit Margin Pelanggan](#24-kontak-rekanan--limit-margin-pelanggan)
   - [2.5 Saldo Awal Sistem (Beginning Balances)](#25-saldo-awal-sistem-beginning-balances)
3. [Modul Pembelian & Hutang Usaha (Procurement / AP)](#3-modul-pembelian--hutang-usaha-procurement--ap)
   - [3.1 Purchase Requisition (PR)](#31-purchase-requisition-pr)
   - [3.2 Purchase Order (PO)](#32-purchase-order-po)
   - [3.3 Penerimaan Barang (GRN)](#33-penerimaan-barang-grn)
   - [3.4 Tagihan Supplier / Faktur AP](#34-tagihan-supplier--faktur-ap)
   - [3.5 Retur Pembelian ke Supplier](#35-retur-pembelian-ke-supplier)
4. [Modul Penjualan & Piutang Usaha (Sales / AR & POS)](#4-modul-penjualan--piutang-usaha-sales--ar--pos)
   - [4.1 Sales Order (SO) & Penugasan Sales](#41-sales-order-so--penugasan-sales)
   - [4.2 Surat Jalan Pengiriman (Delivery Order / DO)](#42-surat-jalan-pengiriman-delivery-order--do)
   - [4.3 Faktur Penjualan (Sales Invoice / AR)](#43-faktur-penjualan-sales-invoice--ar)
   - [4.4 Point of Sale (POS) & Shift Kasir](#44-point-of-sale-pos--shift-kasir)
   - [4.5 Retur Penjualan dari Pelanggan](#45-retur-penjualan-dari-pelanggan)
   - [4.6 Laporan & Rekapitulasi Komisi Sales](#46-laporan--rekapitulasi-komisi-sales)
5. [Modul Manufaktur & Produksi](#5-modul-manufaktur--produksi)
   - [5.1 Bill of Materials (BOM)](#51-bill-of-materials-bom)
   - [5.2 Routing Proses Produksi](#52-routing-proses-produksi)
   - [5.3 Work Orders (SPK Produksi)](#53-work-orders-spk-produksi)
6. [Modul Manajemen Inventori & Stok](#6-modul-manajemen-inventori--stok)
   - [6.1 Kartu Stok & Mutasi Real-Time](#61-kartu-stok--mutasi-real-time)
   - [6.2 Transfer Antar Gudang](#62-transfer-antar-gudang)
   - [6.3 Penyesuaian Stok (Stock Adjustment / Opname)](#63-penyesuaian-stok-stock-adjustment--opname)
   - [6.4 Format Tampilan Stok Multi-UOM](#64-format-tampilan-stok-multi-uom)
7. [Modul Keuangan & Kas/Bank (Finance)](#7-modul-keuangan--kasbank-finance)
   - [7.1 Kas Masuk (Cash In) & Split Akun Target](#71-kas-masuk-cash-in--split-akun-target)
   - [7.2 Kas Keluar (Cash Out) & Split Multi-Rekening](#72-kas-keluar-cash-out--split-multi-rekening)
   - [7.3 Deposit Pelanggan (Customer Deposit)](#73-deposit-pelanggan-customer-deposit)
   - [7.4 Aset Tetap & Depresiasi Otomatis](#74-aset-tetap--depresiasi-otomatis)
8. [Modul Akuntansi & Laporan Keuangan (Accounting)](#8-modul-akuntansi--laporan-keuangan-accounting)
   - [8.1 Jurnal Umum & Jurnal Otomatis](#81-jurnal-umum--jurnal-otomatis)
   - [8.2 Buku Besar (General Ledger)](#82-buku-besar-general-ledger)
   - [8.3 Neraca Saldo (Trial Balance)](#83-neraca-saldo-trial-balance)
   - [8.4 Laporan Laba Rugi (Profit & Loss)](#84-laporan-laba-rugi-profit--loss)
   - [8.5 Laporan Neraca (Balance Sheet)](#85-laporan-neraca-balance-sheet)
   - [8.6 Analisa Umur Piutang & Hutang (AR/AP Aging)](#86-analisa-umur-piutang--hutang-arap-aging)
9. [Pemeliharaan Sistem & Database (Maintenance)](#9-pemeliharaan-sistem--database-maintenance)
   - [9.1 Export / Backup Database SQL](#91-export--backup-database-sql)
   - [9.2 Import / Restore Database SQL](#92-import--restore-database-sql)
   - [9.3 Reset Transaksi Operasional](#93-reset-transaksi-operasional)

---

## 1. Arsitektur & Ikhtisar Sistem
MYCRUD ERP adalah platform ERP berbasis web modern dengan arsitektur double-entry accounting otomatis, real-time perpetual inventory valuation (FIFO/Moving Average), dan multi-entity structure (Multi-Cabang, Multi-Gudang, Multi-UOM).

```
   +-----------------------------------------------------------------------+
   |                     MYCRUD ERP ENTERPRISE CORE                        |
   +-----------------------------------------------------------------------+
        |                  |                    |                   |
   [Procurement]        [Sales]           [Inventory]         [Finance]
   - PR / PO            - SO / DO         - Multi-Warehouse   - Cash In / Out
   - GRN                - AR Invoices     - Multi-UOM Conv    - Split Payment
   - AP Invoices        - POS Kasir Shift - Stock Transfer    - Deposits
   - Purchase Return    - Sales Return    - Adjustments       - Fixed Assets
        |                  |                    |                   |
        +------------------+--------------------+-------------------+
                                   |
                       [Automated Double-Entry GL]
                                   |
                    [Laporan Keuangan & Analitik]
                    - Laba Rugi / Neraca / Neraca Saldo
                    - Buku Besar / Aging AR & AP / Komisi Sales
```

---

## 2. Master Data & Pengaturan Awal

### 2.1 Chart of Accounts (COA) & GL Mapping
- **Struktur Akun:**
  - `1000 - 1999`: Aset (Kas, Bank, Piutang Usaha 1101, Persediaan Barang 1201, Aset Tetap 1501).
  - `2000 - 2999`: Kewajiban (Hutang Usaha 2001, PPN Keluaran 2003, Hutang Gaji 2101).
  - `3000 - 3999`: Ekuitas (Modal Saham, Laba Ditahan, Saldo Awal Ekuitas 3201).
  - `4000 - 4999`: Pendapatan (Penjualan 4001/4101, Retur Penjualan 4102, Pendapatan Jasa 4201).
  - `5000 - 5999`: Harga Pokok Penjualan (HPP Barang Dagang 5001/5101).
  - `6000 - 6999`: Beban Operasional & Administrasi.

### 2.2 Master Barang, Jasa & Multi-UOM
- **Tipe Barang:** `raw_material` (Bahan Baku), `finished_good` (Barang Jadi), `manufactured_good` (Barang Produksi), `service` (Jasa), `bundle` (Paket Komposit).
- **Multi-UOM & Penjualan Daging / Komoditas Berat (Dual UOM: Koli & KG):**
  - Satuan Dasar (*Base Unit*): misal `KG` atau `PCS`.
  - Satuan Kemasan (*Packing Unit / Koli*): misal `KOLI`, `DUS = 24 PCS`, `BOX = 12 PCS`.
  - **Penjualan Daging (Catch Weight):** Penjualan daging memiliki karakteristik 2 satuan sekaligus:
    1. **Koli (Kemasan / Karung / Box):** Mencatat kuantitas fisik kemasan (misal 8 Koli).
    2. **KG (Berat Aktual):** Mencatat total berat riil daging (misal 500 KG). Total harga dihitung berdasarkan `Total Berat (KG) x Harga per KG`.
  - Tampilan stok otomatis menampilkan breakdown kemasan: misal saldo `26 PCS` terformat menjadi `1 Dus 2 Pcs (26 Pcs)`.

### 2.3 Multi-Cabang & Multi-Gudang
- Setiap cabang dapat memiliki rekening kas utama, rekening setoran/deposit, dan daftar harga barang tersendiri.
- Setiap gudang memisahkan saldo fisik stok dan mutasi kartu stok secara independen.

### 2.4 Kontak Rekanan & Limit Margin Pelanggan
- Tipe Kontak: `supplier`, `customer`, `both`.
- **Fitur Limit Margin Khusus Pelanggan (`min_margin_percent`):**
  - Memberikan batas margin minimum khusus untuk customer tertentu (misal Customer VIP = 5%, sedangkan standar umum = 15%).
  - Saat input SO, Invoice, atau POS, sistem otomatis memvalidasi harga terhadap margin khusus pelanggan tersebut.

### 2.5 Saldo Awal Sistem (Beginning Balances)
- Dapat diinput manual atau di-import via Excel template:
  - Saldo Awal Kas & Bank (COA)
  - Saldo Awal Persediaan Barang & Nilai Valuasi
  - Saldo Awal Piutang Usaha (AR) per Pelanggan
  - Saldo Awal Hutang Usaha (AP) per Supplier
- Otomatis membuat Jurnal Saldo Awal berpasangan dengan akun Ekuitas Saldo Awal.

---

## 3. Modul Pembelian & Hutang Usaha (Procurement / AP)

### 3.1 Purchase Requisition (PR)
- Pengajuan permintaan pembelian dari divisi/cabang internal.
- Melalui alur persetujuan (*Approval Workflow*) oleh manajer sebelum dikonversi menjadi PO.

### 3.2 Purchase Order (PO)
- Surat Pesanan Resmi ke Supplier.
- Mendukung pemilihan multi-satuan UOM & Koli (misal beli 8 Koli @ 500 KG).
- Status: `draft`, `approved`, `partial`, `completed`, `cancelled`.

### 3.3 Penerimaan Barang (Goods Receipt / GRN)
- Pencatatan fisik barang masuk ke gudang tujuan berdasarkan PO atau penerimaan langsung (termasuk kolom Koli).
- **Jurnal Otomatis:**
  - `Debit`: Akun Persediaan (1201 / Unbilled GRN 1301)
  - `Kredit`: Akun Hutang Belum Ditagih (Unbilled GRN 2002)

### 3.4 Tagihan Supplier / Faktur AP
- Pengakuan hutang resmi dari faktur vendor/supplier (dapat ditarik dari GRN atau input langsung).
- **Jurnal Otomatis:**
  - `Debit`: Akun Persediaan / Beban Pembelian
  - `Debit`: Akun PPN Masukan (1103 / 2003)
  - `Kredit`: Akun Hutang Usaha AP (2001)

### 3.5 Retur Pembelian ke Supplier
- Pengembalian barang cacat/rusak ke pihak vendor.
- **Pilihan Kompensasi:**
  1. *Potong Hutang Usaha (AP):* Otomatis mengurangi sisa tagihan Faktur AP.
  2. *Pengembalian Kas/Bank:* Mencatat penerimaan uang refund ke rekening kas/bank pilihan.
- **Dampak Stok & Jurnal:**
  - Stok gudang otomatis berkurang (*Stock Out*).
  - `Debit`: Hutang Usaha (2001) / Rekening Kas Refund (1001)
  - `Kredit`: Persediaan Barang (1201) & PPN Masukan.

---

## 4. Modul Penjualan & Piutang Usaha (Sales / AR & POS)

### 4.1 Sales Order (SO) & Penugasan Sales
- Pencatatan pesanan penjualan dari customer dengan dukungan kolom Koli dan Kuantitas (KG).
- **Field Staf Sales (`salesperson_id`):** Menetapkan sales penanggung jawab transaksi beserta persentase komisi yang berlaku.
- **Validasi Minimum Margin:** Memastikan harga jual tidak di bawah HPP + % margin minimum (atau margin khusus pelanggan).

### 4.2 Surat Jalan Pengiriman (Delivery Order / DO)
- Dokumen instruksi pengeluaran barang dan bukti serah terima ke ekspedisi/pelanggan lengkap dengan jumlah Koli fisik.
- Memotong stok gudang dan mencatat COGS/HPP barang keluar.

### 4.3 Faktur Penjualan (Sales Invoice / AR)
- Tagihan piutang resmi ke customer.
- **Jurnal Otomatis:**
  - `Debit`: Piutang Usaha (1101)
  - `Kredit`: Pendapatan Penjualan (4001 / 4101)
  - `Kredit`: PPN Keluaran (2003)
  - *Direct Sale:* `Debit` HPP (5001) & `Kredit` Persediaan (1201).

### 4.4 Point of Sale (POS) & Shift Kasir
- Antarmuka kasir cepat untuk transaksi ritel/toko dengan dukungan barcode scanner dan thermal receipt printer.
- **Dukungan Penjualan Daging & Dual UOM (Koli & KG):**
  - Kasir dapat menginput jumlah **Koli (Box / Karung)** dan kuantitas **Berat (KG)** pada setiap baris item keranjang.
  - Contoh: Kasir menginput `8 Koli` dan `500 KG` daging dengan harga per KG. Sistem menghitung total tagihan `500 KG x Rp/KG = Total`, dan mencatat `8 Koli` pada invoice dan struk kasir.
  - Ringkasan keranjang menampilkan total Koli keseluruhan (misal: `500 KG (1 Item) • 8 Koli`).
  - Struk Thermal Kasir mencetak rincian kuantitas berat beserta jumlah koli secara otomatis (contoh: `500 KG (8 Koli) x Rp 100.000 = Rp 50.000.000`).
- **Manajemen Shift:** Buka shift dengan modal awal (*Starting Cash*), setoran kas tengah hari (*Cash Drop*), dan tutup shift (*Close Shift & Z-Report*).

### 4.5 Retur Penjualan dari Pelanggan
- Pengembalian barang dari customer.
- **Pilihan Kompensasi:**
  1. *Potong Piutang Usaha (AR):* Mengurangi saldo tagihan piutang customer.
  2. *Pengembalian Kas (Cash Refund):* Mengeluarkan uang tunai dari kas/bank toko.
- **Dampak Stok & Jurnal:**
  - Stok gudang otomatis bertambah kembali (*Stock In*).
  - `Debit`: Retur Penjualan (4102) & PPN Keluaran
  - `Credit`: Piutang Usaha (1101) / Kas Refund
  - `Debit`: Persediaan Barang (1201) & `Kredit`: Koreksi HPP (5101).

### 4.6 Laporan & Rekapitulasi Komisi Sales
- Menu: `Sales -> Laporan Komisi Sales`.
- Menghitung omset penjualan per staf sales, persentase komisi, dan nominal komisi yang berhak dicairkan secara akurat berdasarkan faktur yang diterbitkan.

---

## 5. Modul Manufaktur & Produksi

### 5.1 Bill of Materials (BOM)
- Formula resep produksi: mendefinisikan komponen bahan baku, kuantitas kebutuhan, dan estimasi biaya per unit barang jadi.

### 5.2 Routing Proses Produksi
- Tahapan proses kerja (Work Center) berurutan dari Tahap A -> B -> C -> D -> Selesai.

### 5.3 Work Orders (SPK Produksi)
- Perintah kerja produksi:
  - Alokasi bahan baku dari gudang.
  - Pelacakan progress per stasiun kerja (*Advance Step*).
  - Penyelesaian SPK (*Complete Work Order*): Otomatis memotong bahan baku, menambah stok barang jadi, dan menjurnal biaya manufaktur ke Persediaan Barang Jadi.

---

## 6. Modul Manajemen Inventori & Stok

### 6.1 Kartu Stok & Mutasi Real-Time
- Setiap pergerakan barang (PO, GRN, SO, DO, POS, Retur, Transfer, Adjustment, Manufaktur) dicatat dalam *Stock Ledger* dengan referensi dokumen lengkap dan saldo berjalan (*Current Stock After*).

### 6.2 Transfer Antar Gudang
- Memindahkan stok dari Gudang Asal ke Gudang Tujuan.
- Status: `draft` -> `posted` (otomatis mengurangi gudang asal dan menambah gudang tujuan).

### 6.3 Penyesuaian Stok (Stock Adjustment / Opname)
- Menyesuaikan selisih stok fisik hasil stock opname.
- Otomatis mencatat jurnal penyesuaian ke akun *Selisih Persediaan / Beban Opname (6000)*.

### 6.4 Format Tampilan Stok Multi-UOM
- Saldo stok di ringkasan gudang otomatis dihitung dan ditampilkan dalam format satuan kemasan dan satuan dasar (contoh: `1 Dus 2 Pcs`).

---

## 7. Modul Keuangan & Kas/Bank (Finance)

### 7.1 Kas Masuk (Cash In) & Split Akun Target
- Penerimaan pembayaran piutang customer, pendapatan lain-lain, atau setoran modal.
- **Fitur Split Payment (Multi-Target):** Menerima pembayaran dengan membagi ke beberapa rekening (misal sebagian ke Kas Toko, sebagian transfer ke Bank BCA).

### 7.2 Kas Keluar (Cash Out) & Split Multi-Rekening
- Pembayaran tagihan faktur AP supplier atau pengeluaran beban operasional.
- **Fitur Split Multi-Rekening:** Membayar tagihan besar menggunakan kombinasi beberapa rekening kas/bank (misal Kas Utama Rp 5.500.000 + Bank BCA Rp 50.000.000) dengan validasi *Live Balance Check*.

### 7.3 Deposit Pelanggan (Customer Deposit)
- Pengelolaan saldo titipan uang muka / deposit customer yang dapat digunakan untuk melunasi faktur di masa depan.

### 7.4 Aset Tetap & Depresiasi Otomatis
- Pencatatan aset tetap perusahaan (Kendaraan, Bangunan, Mesin, Peralatan Kantor).
- Menjalankan depresiasi otomatis bulanan dengan metode Garis Lurus (*Straight-Line Method*).

---

## 8. Modul Akuntansi & Laporan Keuangan (Accounting)

### 8.1 Jurnal Umum & Jurnal Otomatis
- Seluruh transaksi operasional terintegrasi langsung ke buku jurnal (*Automated Double-Entry*). Pengguna juga dapat membuat *Manual General Journal* untuk penyesuaian khusus.

### 8.2 Buku Besar (General Ledger)
- Rincian mutasi debit/kredit dan saldo berjalan untuk setiap akun COA.

### 8.3 Neraca Saldo (Trial Balance)
- Rekapitulasi saldo debit dan kredit seluruh akun COA untuk memastikan keseimbangan neraca.

### 8.4 Laporan Laba Rugi (Profit & Loss)
- Menyajikan total Pendapatan Penjualan, HPP, Laba Kotor (*Gross Profit*), Beban Operasional, dan Laba Bersih (*Net Profit*).

### 8.5 Laporan Neraca (Balance Sheet)
- Menyajikan posisi keuangan: `Total Aset = Total Kewajiban + Total Ekuitas`.

### 8.6 Analisa Umur Piutang & Hutang (AR/AP Aging)
- Klasifikasi jatuh tempo tagihan: Belum Jatuh Tempo, 1-30 Hari, 31-60 Hari, 61-90 Hari, dan >90 Hari.

---

## 9. Pemeliharaan Sistem & Database (Maintenance)

### 9.1 Export / Backup Database SQL
- Menu: `Maintenance -> Backup & Reset`.
- Menghasilkan dan mengunduh snapshot cadangan database lengkap berformat `.sql` dalam hitungan detik.

### 9.2 Import / Restore Database SQL
- Mengunggah file cadangan `.sql` untuk memulihkan seluruh struktur dan data database sistem secara aman.

### 9.3 Reset Transaksi Operasional
- Menghapus riwayat transaksi operasional (Penjualan, Pembelian, POS, Mutasi Stok, Kas/Bank, Jurnal) saat persiapan *Go-Live* produksi, dengan **menjaga 100% keutuhan seluruh Master Data** (COA, Barang, Kontak, Merk, Cabang, Gudang, Akun Pengguna).
- Dilengkapi pengaman kata kunci konfirmasi: `RESET TRANSAKSI`.

---

## 10. Fitur Cepat Penginputan Transaksi (Smart Autocomplete & Shortcut)

### 10.1 Input Barang Tanpa Select2 (Inline Autocomplete)
- Menggantikan dropdown Select2 yang berat dengan input teks standar yang responsif dan cepat.
- **Trigger Minimal 3 Karakter**: Daftar referensi dan saran barang baru akan muncul mengambang (*floating dropdown*) setelah pengguna mengetik minimal 3 karakter kode barang, nama barang, atau barcode.
- **Dukungan Navigasi Keyboard Penuh**:
  - `Panah Atas / Bawah` : Navigasi memilih barang pada dropdown saran.
  - `Enter` : Memilih barang aktif, mengisi data otomatis, harga satuan, dan kalkulasi subtotal.
  - `Escape` : Menutup dropdown saran.
- **Dukungan Modal Katalog Visual**: Tombol kaca pembesar `[ 🔍 ]` di samping kolom tetap tersedia untuk pengguna yang ingin menjelajah katalog lengkap dalam pop-up modal.

---

*Dokumentasi ini disusun dan dipelihara secara terpusat untuk operasional PT MYCRUD Transformasi Digital.*
