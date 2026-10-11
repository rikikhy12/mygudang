@extends('layouts.app')

@section('title', 'Buat Retur Pembelian')
@section('page_title', 'Formulir Retur Pembelian (Purchase Return)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Buat Retur Pembelian ke Supplier</h2>
            <p class="text-muted fs-7 mb-0">Pengembalian barang rusak / salah kirim ke supplier beserta potong hutang AP atau pengembalian kas</p>
        </div>
        <a href="{{ route('procurement.returns.index') }}" class="btn btn-sm btn-light fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Retur
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-left text-danger fs-3"></i> Formulir Retur Pembelian & Pengurangan Stok
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Pilih supplier, gudang asal barang dikeluarkan, metode pengembalian dana/potong hutang, dan rincian barang yang diretur
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('procurement.returns.store') }}" id="returnForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1: Informasi Header -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-danger fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Faktur & Supplier</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Tarik dari Faktur AP <span class="badge badge-light-secondary fs-9">Opsional</span></label>
                        <select name="purchase_invoice_id" id="purchase_invoice_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Faktur AP (Jika ada) --" onchange="window.location.href='{{ route('procurement.returns.create') }}' + (this.value ? '?purchase_invoice_id=' + this.value : '')">
                            <option value="">-- Mode Langsung: Tanpa Faktur --</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}" {{ $selectedInvoice && $selectedInvoice->id == $inv->id ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} - {{ $inv->supplier->name ?? 'Supplier' }} (Sisa Hutang: Rp {{ number_format($inv->grand_total - $inv->paid_amount, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text fs-9 text-muted">Memilih faktur akan otomatis mengisi data supplier dan item dari tagihan tersebut.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Supplier / Pemasok</label>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Supplier --" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ ($selectedInvoice && $selectedInvoice->supplier_id == $s->id) || old('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->code }} - {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Gudang Pengeluaran Barang</label>
                        <select name="warehouse_id" id="warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->code }} - {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text fs-9 text-danger">Stok barang akan otomatis dipotong keluar dari gudang ini.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Cabang</label>
                        <select name="branch_id" id="branch_id" class="form-select form-select-solid form-select-sm" data-control="select2">
                            <option value="">-- Pusat / Semua Cabang --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Retur</label>
                        <input type="date" name="return_date" class="form-control form-control-solid form-control-sm" value="{{ old('return_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Metode Pengembalian / Kompensasi</label>
                        <select name="refund_method" id="refund_method" class="form-select form-select-solid form-select-sm" required onchange="toggleRefundFields()">
                            <option value="potong_hutang" {{ old('refund_method') === 'potong_hutang' ? 'selected' : '' }}>Potong Hutang AP (Mengurangi tagihan supplier)</option>
                            <option value="tunai_kas" {{ old('refund_method') === 'tunai_kas' ? 'selected' : '' }}>Pengembalian Kas / Bank (Supplier transfer balik)</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="cashAccountSection" style="display: none;">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Rekening Kas / Bank Penerima Refund</label>
                        <select name="cash_account_id" id="cash_account_id" class="form-select form-select-solid form-select-sm">
                            <option value="">-- Pilih Akun Kas/Bank --</option>
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}" {{ old('cash_account_id') == $ca->id ? 'selected' : '' }}>
                                    {{ $ca->code }} - {{ $ca->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Alasan Retur</label>
                        <textarea name="notes" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Tuliskan keterangan detail retur barang...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Section 2: Item Retur -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-danger fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rincian Barang yang Diretur</h4>
                    </div>
                    @if(!$selectedInvoice)
                    <button type="button" class="btn btn-xs btn-light-danger fw-bold" onclick="addManualRow()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                    </button>
                    @endif
                </div>

                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="itemsTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="min-width: 240px;">Barang / Material</th>
                                <th class="text-center" style="width: 120px;">Koli / Bal</th>
                                <th class="text-center" style="width: 130px;">Berat Retur (Kg)</th>
                                <th class="text-end" style="width: 170px;">Harga Satuan / Kg (Rp)</th>
                                <th class="text-end" style="width: 170px;">Subtotal (Rp)</th>
                                <th style="width: 180px;">Alasan Retur</th>
                                <th class="text-center pe-4" style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody" class="text-gray-700 fw-semibold">
                            @if($selectedInvoice)
                                @foreach($selectedInvoice->items as $idx => $invItem)
                                <tr>
                                    <td class="ps-4">
                                        <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $invItem->item_id }}">
                                        <div class="fw-bold text-gray-900">{{ $invItem->item->code }} - {{ $invItem->item->name }}</div>
                                        <span class="badge badge-light-secondary fs-9">Satuan: {{ $invItem->item->unit->name ?? 'Kg' }}</span>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][koli]" class="form-control form-control-solid form-control-sm text-center" value="{{ $invItem->koli ?? '' }}" placeholder="e.g. 1 Bal">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $invItem->quantity }}" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm text-center qty-calc" value="0" required oninput="calculateTotal()">
                                        <span class="text-muted fs-9 d-block text-center mt-1">Maks: {{ $invItem->quantity }}</span>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" class="form-control form-control-solid form-control-sm text-end price-calc" value="{{ $invItem->unit_price }}" required oninput="calculateTotal()">
                                    </td>
                                    <td class="text-end">
                                        <input type="text" class="form-control form-control-solid form-control-sm text-end fw-bold line-total" readonly value="Rp 0">
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][reason]" class="form-control form-control-solid form-control-sm" placeholder="Cth: Cacat / Rusak">
                                    </td>
                                    <td class="text-center pe-4">
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Section 3: Ringkasan Total -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-danger fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Ringkasan Nilai Retur</h4>
                </div>

                <div class="row justify-content-between mb-4">
                    <div class="col-md-6">
                        <div class="alert alert-light-danger d-flex align-items-start p-4 rounded-3 border border-danger border-dashed">
                            <i class="bi bi-info-circle-fill fs-2 text-danger me-3 mt-1"></i>
                            <div class="d-flex flex-column fs-7">
                                <strong class="text-gray-900 mb-1">Dampak Retur Pembelian:</strong>
                                <span>1. <strong>Stok Gudang:</strong> Berkurang otomatis secara instan sesuai Qty yang diretur.</span>
                                <span>2. <strong>Faktur AP:</strong> Otomatis mengurangi sisa tagihan jika memilih metode <em>Potong Hutang AP</em>.</span>
                                <span>3. <strong>Akuntansi:</strong> Jurnal otomatis Debit Hutang Usaha / Kas dan Kredit Persediaan Barang.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="bg-light p-4 rounded-3 border border-gray-300">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Subtotal Barang Diretur:</span>
                                <span class="text-gray-900 fw-bold fs-7" id="dispSubtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Pajak PPN (%):</span>
                                <div class="d-flex align-items-center gap-1" style="width: 140px;">
                                    <input type="number" step="0.1" min="0" max="100" name="tax_rate" id="tax_rate" class="form-control form-control-solid form-control-sm text-end" value="0" oninput="calculateTotal()">
                                    <span class="fs-8 text-muted">%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Nominal PPN:</span>
                                <span class="text-gray-800 fw-bold fs-7" id="dispTaxAmount">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-400">
                                <span class="text-gray-900 fw-bolder fs-6">Total Nilai Retur:</span>
                                <span class="text-danger fw-bolder fs-4" id="dispGrandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer d-flex justify-content-end gap-3 p-6 bg-light border-top">
                <a href="{{ route('procurement.returns.index') }}" class="btn btn-light fw-bold">Batal</a>
                <button type="submit" class="btn btn-danger fw-bold" id="submitBtn">
                    <i class="bi bi-check-circle me-1"></i> Simpan & Posting Retur Pembelian
                </button>
            </div>
        </form>
    </div>
</div>

<template id="manualRowTemplate">
    <tr>
        <td class="ps-4">
            <select name="items[INDEX][item_id]" class="form-select form-select-solid form-select-sm item-select" required onchange="handleItemSelect(this)">
                <option value="">-- Pilih Barang --</option>
                @foreach($items as $it)
                    <option value="{{ $it->id }}" data-price="{{ $it->purchase_price }}" data-unit="{{ $it->unit->name ?? 'Kg' }}">
                        {{ $it->code }} - {{ $it->name }}
                    </option>
                @endforeach
            </select>
            <span class="badge badge-light-secondary fs-9 unit-badge mt-1 d-none">Satuan: -</span>
        </td>
        <td>
            <input type="text" name="items[INDEX][koli]" class="form-control form-control-solid form-control-sm text-center" placeholder="e.g. 1 Bal">
        </td>
        <td>
            <input type="number" step="0.01" min="0.01" name="items[INDEX][quantity]" class="form-control form-control-solid form-control-sm text-center qty-calc" value="1" required oninput="calculateTotal()">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="items[INDEX][unit_price]" class="form-control form-control-solid form-control-sm text-end price-calc" value="0" required oninput="calculateTotal()">
        </td>
        <td class="text-end">
            <input type="text" class="form-control form-control-solid form-control-sm text-end fw-bold line-total" readonly value="Rp 0">
        </td>
        <td>
            <input type="text" name="items[INDEX][reason]" class="form-control form-control-solid form-control-sm" placeholder="Cth: Cacat / Rusak">
        </td>
        <td class="text-center pe-4">
            <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let rowIndex = {{ $selectedInvoice ? count($selectedInvoice->items) : 0 }};

function toggleRefundFields() {
    const method = document.getElementById('refund_method').value;
    const cashSec = document.getElementById('cashAccountSection');
    const cashSelect = document.getElementById('cash_account_id');
    if (method === 'tunai_kas') {
        cashSec.style.display = 'block';
        cashSelect.setAttribute('required', 'required');
    } else {
        cashSec.style.display = 'none';
        cashSelect.removeAttribute('required');
    }
}

function addManualRow() {
    const tpl = document.getElementById('manualRowTemplate').innerHTML;
    const rendered = tpl.replace(/INDEX/g, rowIndex++);
    const tbody = document.getElementById('itemsTableBody');
    const tempDiv = document.createElement('tbody');
    tempDiv.innerHTML = rendered;
    const newRow = tempDiv.firstElementChild;
    tbody.appendChild(newRow);
    
    if (window.$ && $.fn.select2) {
        $(newRow).find('.item-select').select2({ width: '100%' });
    }
    calculateTotal();
}

function handleItemSelect(selectElem) {
    const opt = selectElem.options[selectElem.selectedIndex];
    const price = opt.getAttribute('data-price') || 0;
    const unit = opt.getAttribute('data-unit') || 'Pcs';
    const row = selectElem.closest('tr');
    
    const priceInput = row.querySelector('.price-calc');
    if (priceInput) {
        priceInput.value = price;
    }
    
    const unitBadge = row.querySelector('.unit-badge');
    if (unitBadge) {
        unitBadge.textContent = 'Satuan: ' + unit;
        unitBadge.classList.remove('d-none');
    }
    calculateTotal();
}

function removeRow(btn) {
    const row = btn.closest('tr');
    row.remove();
    calculateTotal();
}

function calculateTotal() {
    let subtotal = 0;
    const rows = document.querySelectorAll('#itemsTableBody tr');
    
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-calc')?.value) || 0;
        const price = parseFloat(row.querySelector('.price-calc')?.value) || 0;
        const line = qty * price;
        subtotal += line;
        
        const lineElem = row.querySelector('.line-total');
        if (lineElem) {
            lineElem.value = 'Rp ' + line.toLocaleString('id-ID');
        }
    });

    const taxRate = parseFloat(document.getElementById('tax_rate')?.value) || 0;
    const taxAmount = (subtotal * taxRate) / 100;
    const grandTotal = subtotal + taxAmount;

    document.getElementById('dispSubtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('dispTaxAmount').textContent = 'Rp ' + taxAmount.toLocaleString('id-ID');
    document.getElementById('dispGrandTotal').textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', function() {
    toggleRefundFields();
    @if(!$selectedInvoice)
        if (document.querySelectorAll('#itemsTableBody tr').length === 0) {
            addManualRow();
        }
    @else
        calculateTotal();
    @endif
});
</script>
@endpush
@endsection
