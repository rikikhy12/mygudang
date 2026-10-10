@extends('layouts.app')

@section('title', 'Saldo Awal Sistem (Beginning Balances)')
@section('page_title', 'Pengaturan Saldo Awal Sistem (Opening Balances)')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fs-4 fw-bold text-gray-900 mb-1">
            <i class="bi bi-box-arrow-in-right text-primary me-2"></i> Input Saldo Awal Sistem (Beginning Balance)
        </h2>
        <p class="text-muted fs-7 mb-0">Masukkan saldo awal barang persediaan, piutang, hutang, dan neraca pembukuan COA dengan jurnal otomatis lawan Modal (3001)</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('accounting.beginning-balances.import') }}" class="btn btn-light-success btn-sm fw-bold shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Import Saldo Awal (Excel / CSV)
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex align-items-center p-4 mb-4 rounded-3">
    <i class="bi bi-check-circle-fill fs-2 text-success me-3"></i>
    <div>
        <h5 class="mb-1 text-success fw-bold">Berhasil!</h5>
        <div class="fs-7 text-gray-800">{{ session('success') }}</div>
    </div>
</div>
@endif

@if(session('error') || $errors->any())
<div class="alert alert-danger d-flex align-items-center p-4 mb-4 rounded-3">
    <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-3"></i>
    <div>
        <h5 class="mb-1 text-danger fw-bold">Terjadi Kesalahan:</h5>
        <div class="fs-7 text-gray-800">{{ session('error') }}</div>
        @if($errors->any())
            <ul class="mb-0 ps-3 fs-7">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif

<div class="card card-flush shadow-sm">
    <div class="card-header border-bottom py-2">
        <!-- Navigation Tabs -->
        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-6 fw-bold">
            <li class="nav-item">
                <a class="nav-link text-active-primary py-4 {{ $tab === 'inventory' ? 'active' : '' }}" href="{{ route('accounting.beginning-balances.index', ['tab' => 'inventory']) }}">
                    <i class="bi bi-boxes fs-4 me-2"></i> 1. Saldo Awal Stok Barang
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-active-primary py-4 {{ $tab === 'ar' ? 'active' : '' }}" href="{{ route('accounting.beginning-balances.index', ['tab' => 'ar']) }}">
                    <i class="bi bi-receipt-cutoff fs-4 me-2"></i> 2. Saldo Awal Piutang Usaha (AR)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-active-primary py-4 {{ $tab === 'ap' ? 'active' : '' }}" href="{{ route('accounting.beginning-balances.index', ['tab' => 'ap']) }}">
                    <i class="bi bi-receipt fs-4 me-2"></i> 3. Saldo Awal Hutang Usaha (AP)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-active-primary py-4 {{ $tab === 'coa' ? 'active' : '' }}" href="{{ route('accounting.beginning-balances.index', ['tab' => 'coa']) }}">
                    <i class="bi bi-journal-check fs-4 me-2"></i> 4. Neraca Saldo Awal Akun (COA)
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-5">
        
        <!-- ============================================================== -->
        <!-- TAB 1: SALDO AWAL STOK BARANG -->
        <!-- ============================================================== -->
        @if($tab === 'inventory')
        <form method="POST" action="{{ route('accounting.beginning-balances.store-inventory') }}" id="formOpeningStock">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fs-7 fw-bold text-gray-700 required">Pilih Gudang Penyimpanan</label>
                    <select name="warehouse_id" class="form-select form-select-sm" required>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->code }} - {{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-7 fw-bold text-gray-700 required">Tanggal Saldo Awal</label>
                    <input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ date('Y-01-01') }}" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="badge badge-light-primary p-3 w-100 fs-7 text-start">
                        <i class="bi bi-info-circle me-1"></i> Jurnal Otomatis: <strong>Debit Persediaan (1201) | Kredit Modal Ekuitas (3001)</strong>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-3 py-3" style="width: 320px;">Kode & Nama Barang Fisik</th>
                            <th class="text-center" style="width: 90px;">Satuan</th>
                            <th class="text-center" style="width: 140px;">Stok Awal (Qty)</th>
                            <th class="text-end" style="width: 190px;">Harga Pokok / HPP (Rp)</th>
                            <th class="text-end pe-3" style="width: 200px;">Nilai Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @php $idx = 0; @endphp
                        @foreach($items as $item)
                        @if($item->isPhysical())
                        <tr>
                            <td class="ps-3">
                                <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                                <span class="fw-bold text-gray-900">{{ $item->code }}</span> - {{ $item->name }}
                                <div class="fs-9 text-muted">{{ $item->category ? $item->category->name : '-' }}</div>
                            </td>
                            <td class="text-center">{{ $item->unit ? $item->unit->symbol : 'PCS' }}</td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm text-center stock-qty-input" value="0" oninput="recomputeOpeningStock()">
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_cost]" class="form-control form-control-sm text-end stock-cost-input" value="{{ $item->purchase_price ?: 0 }}" oninput="recomputeOpeningStock()">
                            </td>
                            <td class="pe-3">
                                <input type="text" class="form-control form-control-sm text-end fw-bold line-stock-total bg-light" readonly value="Rp 0">
                            </td>
                        </tr>
                        @php $idx++; @endphp
                        @endif
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light-primary fw-bolder text-gray-900 fs-7 border-top">
                        <tr>
                            <td colspan="4" class="ps-3 py-3 text-end">TOTAL NILAI SALDO AWAL PERSEDIAAN:</td>
                            <td class="text-end pe-3 py-3 text-primary fs-5" id="grandStockTotal">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm fw-bolder px-5">
                    <i class="bi bi-save me-1"></i> Simpan Saldo Awal Stok & Posting Jurnal
                </button>
            </div>
        </form>

        <!-- ============================================================== -->
        <!-- TAB 2: SALDO AWAL PIUTANG USAHA (AR) -->
        <!-- ============================================================== -->
        @elseif($tab === 'ar')
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card bg-light border p-4">
                    <h4 class="fw-bold text-gray-900 mb-3">
                        <i class="bi bi-plus-circle text-primary me-2"></i> Input Faktur Saldo Awal Piutang
                    </h4>
                    <form method="POST" action="{{ route('accounting.beginning-balances.store-ar') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold required">Customer / Pelanggan</label>
                            <select name="customer_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold required">Nomor Faktur Masa Lalu</label>
                            <input type="text" name="invoice_number" class="form-control form-control-sm" placeholder="contoh: INV-LALU-001" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold required">Tgl Faktur</label>
                                <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold required">Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold required">Nominal Sisa Piutang (Rp)</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm text-end fw-bold" placeholder="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-semibold">Catatan / Keterangan</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Faktur penjualan sebelum migrasi ke ERP">
                        </div>
                        <div class="alert alert-info py-2 px-3 fs-8 mb-3">
                            Jurnal: <strong>Debit: Piutang Usaha (1101) | Kredit: Modal Saldo Awal (3001)</strong>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                            <i class="bi bi-save me-1"></i> Simpan Saldo Awal Piutang
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-4 h-100">
                    <h5 class="fw-bold text-gray-800 mb-2">Penjelasan Saldo Awal Piutang</h5>
                    <p class="text-muted fs-7">
                        Fitur ini digunakan saat pertama kali berpindah ke MYGUDANG ERP untuk mencatatkan seluruh faktur penjualan lama yang belum lunas (outstanding).
                    </p>
                    <ul class="text-gray-700 fs-7 mb-4">
                        <li>Faktur saldo awal akan langsung masuk ke daftar <strong>Faktur Penjualan (AR)</strong> dan <strong>Laporan Aging Piutang</strong>.</li>
                        <li>Saat customer membayar faktur ini di masa depan, Anda cukup mencatatnya via menu <strong>Kas Masuk (Cash In)</strong> seperti biasa.</li>
                        <li>Sistem otomatis menjurnal Piutang Usaha bertambah di Debit dan diimbangi oleh Modal Ekuitas Saldo Awal (3001) di Kredit.</li>
                    </ul>
                    <a href="{{ route('accounting.reports.ar-aging') }}" class="btn btn-light-primary btn-sm fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Lihat Laporan Aging Piutang
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 3: SALDO AWAL HUTANG USAHA (AP) -->
        <!-- ============================================================== -->
        @elseif($tab === 'ap')
        <div class="row g-4">
            <div class="col-md-5">
                <div class="card bg-light border p-4">
                    <h4 class="fw-bold text-gray-900 mb-3">
                        <i class="bi bi-plus-circle text-danger me-2"></i> Input Tagihan Saldo Awal Hutang
                    </h4>
                    <form method="POST" action="{{ route('accounting.beginning-balances.store-ap') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold required">Supplier / Pemasok</label>
                            <select name="supplier_id" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->code }} - {{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold required">No. Tagihan Sistem</label>
                                <input type="text" name="invoice_number" class="form-control form-control-sm" placeholder="contoh: PINV-AWAL-001" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-7 fw-semibold">No. Faktur Supplier</label>
                                <input type="text" name="supplier_invoice_number" class="form-control form-control-sm" placeholder="INV/SUP/123">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold required">Tgl Tagihan</label>
                                <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-01') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fs-7 fw-bold required">Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-bold required">Nominal Sisa Hutang (Rp)</label>
                            <input type="number" step="0.01" min="1" name="amount" class="form-control form-control-sm text-end fw-bold" placeholder="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 fw-semibold">Catatan / Keterangan</label>
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Tagihan pembelian sebelum migrasi ke ERP">
                        </div>
                        <div class="alert alert-danger py-2 px-3 fs-8 mb-3">
                            Jurnal: <strong>Debit: Modal Saldo Awal (3001) | Kredit: Hutang Usaha (2001)</strong>
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold">
                            <i class="bi bi-save me-1"></i> Simpan Saldo Awal Hutang
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-7">
                <div class="border rounded p-4 h-100">
                    <h5 class="fw-bold text-gray-800 mb-2">Penjelasan Saldo Awal Hutang Supplier</h5>
                    <p class="text-muted fs-7">
                        Fitur ini digunakan untuk mencatat kewajiban tagihan kepada supplier yang belum dibayarkan sebelum sistem mulai digunakan.
                    </p>
                    <ul class="text-gray-700 fs-7 mb-4">
                        <li>Tagihan akan langsung tercatat pada menu <strong>Tagihan Supplier (AP)</strong> dan <strong>Laporan Aging Hutang</strong>.</li>
                        <li>Pelunasan tagihan ini dapat dilakukan melalui menu <strong>Kas Keluar (Cash Out)</strong>.</li>
                        <li>Sistem otomatis menjurnal Hutang Usaha (2001) di Kredit dan Modal Saldo Awal (3001) di Debit.</li>
                    </ul>
                    <a href="{{ route('accounting.reports.ap-aging') }}" class="btn btn-light-danger btn-sm fw-semibold">
                        <i class="bi bi-clock-history me-1"></i> Lihat Laporan Aging Hutang
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 4: NERACA SALDO AWAL AKUN (COA) -->
        <!-- ============================================================== -->
        @elseif($tab === 'coa')
        <form method="POST" action="{{ route('accounting.beginning-balances.store-coa') }}" id="formOpeningCoa">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fs-7 fw-bold text-gray-700 required">Tanggal Neraca Saldo Awal</label>
                    <input type="date" name="transaction_date" class="form-control form-control-sm" value="{{ date('Y-01-01') }}" required>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <div class="alert alert-primary py-2 px-3 m-0 fs-8 w-100">
                        <i class="bi bi-scale me-1"></i> Masukkan saldo debit & kredit untuk semua akun COA. Total Debit harus sama dengan Total Kredit (Balance).
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-3 py-3" style="width: 120px;">Kode Akun</th>
                            <th class="py-3">Nama Akun COA</th>
                            <th class="py-3" style="width: 130px;">Tipe Akun</th>
                            <th class="text-end py-3" style="width: 200px;">Saldo Debit (Rp)</th>
                            <th class="text-end pe-3 py-3" style="width: 200px;">Saldo Kredit (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700">
                        @foreach($accounts as $acc)
                        <tr>
                            <td class="ps-3 font-monospace fw-bold text-gray-900">{{ $acc->code }}</td>
                            <td>
                                <span class="fw-bold text-gray-800">{{ $acc->name }}</span>
                                @if($acc->is_cash_or_bank)
                                    <span class="badge badge-light-success fs-9 ms-1">Kas/Bank</span>
                                @endif
                            </td>
                            <td><span class="badge badge-light-secondary fs-8 text-uppercase">{{ $acc->type }}</span></td>
                            <td>
                                <input type="number" step="0.01" min="0" name="balances[{{ $acc->id }}][debit]" class="form-control form-control-sm text-end coa-debit-input" value="0" oninput="recomputeCoaTotals()">
                            </td>
                            <td class="pe-3">
                                <input type="number" step="0.01" min="0" name="balances[{{ $acc->id }}][credit]" class="form-control form-control-sm text-end coa-credit-input" value="0" oninput="recomputeCoaTotals()">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light fw-bolder text-gray-900 fs-7 border-top border-2 border-primary">
                        <tr>
                            <td colspan="3" class="ps-3 py-3 text-end">TOTAL NERACA SALDO AWAL:</td>
                            <td class="text-end py-3 text-primary fs-6" id="totalCoaDebit">Rp 0</td>
                            <td class="text-end pe-3 py-3 text-primary fs-6" id="totalCoaCredit">Rp 0</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="ps-3 py-2 text-end">SELISIH / STATUS BALANCE:</td>
                            <td colspan="2" class="text-end pe-3 py-2 fs-6" id="coaBalanceStatus">
                                <span class="badge badge-light-success fs-7">Balance (Rp 0)</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm fw-bolder px-5" id="btnSubmitCoa">
                    <i class="bi bi-save me-1"></i> Simpan & Posting Neraca Saldo Awal
                </button>
            </div>
        </form>
        @endif

    </div>
</div>

@section('scripts')
<script>
    function formatRupiah(num) {
        return 'Rp ' + Number(Math.round(num)).toLocaleString('id-ID');
    }

    function recomputeOpeningStock() {
        let grandTotal = 0;
        document.querySelectorAll('tbody tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('.stock-qty-input')?.value) || 0;
            const c = parseFloat(tr.querySelector('.stock-cost-input')?.value) || 0;
            const line = q * c;
            grandTotal += line;
            const lineTotalEl = tr.querySelector('.line-stock-total');
            if (lineTotalEl) lineTotalEl.value = formatRupiah(line);
        });

        const grandEl = document.getElementById('grandStockTotal');
        if (grandEl) grandEl.innerText = formatRupiah(grandTotal);
    }

    function recomputeCoaTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.coa-debit-input').forEach(input => {
            totalDebit += parseFloat(input.value) || 0;
        });

        document.querySelectorAll('.coa-credit-input').forEach(input => {
            totalCredit += parseFloat(input.value) || 0;
        });

        const debitEl = document.getElementById('totalCoaDebit');
        const creditEl = document.getElementById('totalCoaCredit');
        const statusEl = document.getElementById('coaBalanceStatus');
        const btnSubmit = document.getElementById('btnSubmitCoa');

        if (debitEl) debitEl.innerText = formatRupiah(totalDebit);
        if (creditEl) creditEl.innerText = formatRupiah(totalCredit);

        const diff = Math.abs(totalDebit - totalCredit);
        if (statusEl) {
            if (diff < 0.01 && (totalDebit > 0 || totalCredit > 0)) {
                statusEl.innerHTML = '<span class="badge badge-light-success fs-7 fw-bold">✓ SEIMBANG (BALANCE)</span>';
                if (btnSubmit) btnSubmit.disabled = false;
            } else if (diff >= 0.01) {
                statusEl.innerHTML = `<span class="badge badge-light-danger fs-7 fw-bold">✗ TIDAK BALANCE (Selisih: ${formatRupiah(diff)})</span>`;
            } else {
                statusEl.innerHTML = '<span class="badge badge-light-secondary fs-7">Belum Diisi</span>';
            }
        }
    }
</script>
@endsection
@endsection
