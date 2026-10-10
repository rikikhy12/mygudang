@extends('layouts.app')

@section('title', 'Catat Faktur Tagihan AP')
@section('page_title', 'Formulir Tagihan Pembelian (Accounts Payable)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Catat Tagihan Supplier (AP)</h2>
            <p class="text-muted fs-7 mb-0">Input tagihan pembelian langsung (tanpa PO/GRN) atau tarik dari dokumen Penerimaan Barang (GRN)</p>
        </div>
        <a href="{{ route('procurement.invoices.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tagihan
        </a>
    </div>

    <!-- Nav Tabs for Mode Selection -->
    <ul class="nav nav-tabs nav-line-tabs fs-6 border-0">
        <li class="nav-item">
            <a class="nav-link {{ empty($selectedGrn) ? 'active fw-bold text-primary' : 'text-muted' }}" href="{{ route('procurement.invoices.create') }}">
                <i class="bi bi-lightning-charge me-1"></i> Input Tagihan Langsung
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ !empty($selectedGrn) ? 'active fw-bold text-primary' : 'text-muted' }}" href="javascript:void(0)" onclick="document.getElementById('grnSelectSection').scrollIntoView({behavior: 'smooth'})">
                <i class="bi bi-box-seam me-1"></i> Tarik dari Dokumen GRN
            </a>
        </li>
    </ul>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-primary fs-3"></i> Formulir Tagihan Pembelian & Hutang (AP)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi data supplier, faktur pemasok, gudang tujuan, dan rincian item pembelian
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('procurement.invoices.store') }}" id="invForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Supplier & Parameter Tagihan</h4>
                </div>

                <div class="row g-3 mb-5" id="grnSelectSection">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Tarik dari Penerimaan Barang (GRN) <span class="badge badge-light-secondary fs-9">Opsional</span></label>
                        <select name="goods_receipt_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Mode Langsung: Tanpa GRN --" onchange="window.location.href='{{ route('procurement.invoices.create') }}' + (this.value ? '?grn_id=' + this.value : '')">
                            <option value="">-- Mode Langsung: Tanpa GRN --</option>
                            @foreach($unbilledGrns as $ugrn)
                                <option value="{{ $ugrn->id }}" {{ $selectedGrn && $selectedGrn->id == $ugrn->id ? 'selected' : '' }}>
                                    {{ $ugrn->grn_number }} - {{ $ugrn->supplier->name }} ({{ $ugrn->receipt_date->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required mb-0">Supplier / Pemasok</label>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-bold fs-8" onclick="openQuickContactModal('supplier', '#supplier_id')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Supplier
                            </button>
                        </div>
                        <select name="supplier_id" id="supplier_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Supplier --" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" {{ ($selectedGrn && $selectedGrn->supplier_id == $s->id) || old('supplier_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->code }} - {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 {{ empty($selectedGrn) ? 'required' : '' }}">Gudang Penerimaan Stok</label>
                        <select name="warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" {{ empty($selectedGrn) ? 'required' : '' }}>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}" {{ ($selectedGrn && $selectedGrn->warehouse_id == $w->id) || old('warehouse_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->code }} - {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Faktur dari Supplier</label>
                        <input type="text" name="supplier_invoice_number" class="form-control form-control-solid form-control-sm" placeholder="Contoh: INV/SUP/2026/0912" value="{{ old('supplier_invoice_number') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Faktur</label>
                        <input type="date" name="invoice_date" class="form-control form-control-solid form-control-sm" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Jatuh Tempo (Due Date)</label>
                        <input type="date" name="due_date" class="form-control form-control-solid form-control-sm" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Pajak PPN (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.1" min="0" max="100" name="tax_rate" id="taxRateInput" class="form-control form-control-solid form-control-sm text-end" value="{{ old('tax_rate', $selectedGrn?->purchaseOrder?->tax_rate ?? 0) }}" oninput="recomputeInvoice()">
                            <span class="input-group-text bg-light border-0 text-muted">%</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="setTaxRate(0)">0%</button>
                            <button type="button" class="btn btn-outline-primary btn-sm px-2" onclick="setTaxRate(11)">11%</button>
                            <button type="button" class="btn btn-outline-primary btn-sm px-2" onclick="setTaxRate(12)">12%</button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nominal Pajak PPN Masukan (Rp)</label>
                        <input type="number" step="0.01" name="tax_amount" id="taxAmountInput" class="form-control form-control-solid form-control-sm text-end" value="0" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Memo Tagihan</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Keterangan tagihan atau syarat pembayaran..." value="{{ old('notes', $selectedGrn?->purchaseOrder?->notes ?? '') }}">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">
                            @if($selectedGrn)
                                Rincian Barang Faktur dari GRN: <span class="text-primary ms-1">{{ $selectedGrn->grn_number }}</span>
                            @else
                                Rincian Barang Tagihan Pembelian Langsung
                            @endif
                        </h4>
                    </div>
                    @if(!$selectedGrn)
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-success fw-semibold" onclick="openQuickItemModal(handleNewItemAdded)">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Master Barang
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                    @endif
                </div>

                @if($selectedGrn)
                    <input type="hidden" name="purchase_order_id" value="{{ $selectedGrn->purchase_order_id }}">
                    <div class="table-responsive rounded border mb-4">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="itemsTable">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4 py-3">Kode & Nama Barang</th>
                                    <th class="text-center" style="width: 110px;">Qty Tagihan</th>
                                    <th class="text-center" style="width: 110px;">Koli (Ket)</th>
                                    <th class="text-end" style="width: 180px;">Harga Satuan (Rp)</th>
                                    <th class="text-end pe-4" style="width: 180px;">Subtotal Baris</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700 fw-semibold">
                                @foreach($selectedGrn->items as $idx => $gItem)
                                @php 
                                    $poItem = $selectedGrn->purchaseOrder ? $selectedGrn->purchaseOrder->items->where('item_id', $gItem->item_id)->first() : null;
                                    $unitPrice = $poItem ? (float) $poItem->unit_price : (float) $gItem->unit_cost;
                                    $line = $gItem->quantity * $unitPrice; 
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $gItem->item_id }}">
                                        <span class="fw-bold text-gray-900">{{ $gItem->item->code }}</span> - {{ $gItem->item->name }}
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm text-center qty-calc" value="{{ $gItem->quantity }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $idx }}][koli]" class="form-control form-control-solid form-control-sm text-center" value="{{ $gItem->koli }}" placeholder="Koli">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" class="form-control form-control-solid form-control-sm text-end price-calc" value="{{ $unitPrice }}" required>
                                    </td>
                                    <td class="pe-4">
                                        <input type="text" class="form-control form-control-solid form-control-sm text-end fw-bold line-total" readonly value="Rp {{ number_format($line, 0, ',', '.') }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Direct AP Input Mode -->
                    <div class="table-responsive rounded border mb-4">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="itemsTable">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th class="ps-4 py-3" style="min-width: 250px;">Pilih Barang / Material</th>
                                    <th class="text-center" style="width: 110px;">Qty</th>
                                    <th class="text-center" style="width: 110px;">Koli (Ket)</th>
                                    <th class="text-end" style="width: 180px;">Harga Satuan (Rp)</th>
                                    <th class="text-end" style="width: 180px;">Subtotal (Rp)</th>
                                    <th class="text-center pe-4" style="width: 60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="text-gray-700 fw-semibold">
                                <!-- Rows injected via JS or default 1 row -->
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Ringkasan Total Tagihan & Hutang</h4>
                </div>

                <div class="row justify-content-end mb-4">
                    <div class="col-md-5">
                        <div class="bg-light p-4 rounded-3 border border-gray-300">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Subtotal Tagihan AP:</span>
                                <span class="text-gray-900 fw-bold fs-7" id="dispSubtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Pajak PPN (<span id="dispTaxRate">0</span>%):</span>
                                <span class="text-gray-800 fw-bold fs-7" id="dispTaxAmount">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-400">
                                <span class="text-gray-900 fw-bolder fs-6">Grand Total Tagihan (AP):</span>
                                <span class="text-primary fw-bolder fs-4" id="dispGrandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-primary d-flex align-items-center p-4 mb-2 rounded-3 fs-7">
                    <i class="bi bi-journal-check fs-2 text-primary me-3"></i>
                    <div class="d-flex flex-column">
                        <strong class="text-gray-800">Jurnal Otomatis Akuntansi:</strong>
                        <span class="text-gray-700">
                            @if($selectedGrn)
                                • <strong>Debit:</strong> Akun <code>1301 - Unbilled GRN / Persediaan</code> | 
                                • <strong>Debit:</strong> Akun <code>2003 - PPN Masukan</code> (jika ada) | 
                                • <strong>Kredit:</strong> Akun <code>2001 - Hutang Usaha (AP)</code>
                        @else
                            • <strong>Debit:</strong> Akun <code>1201 - Persediaan Barang</code> (Stok bertambah otomatis & Moving Average dihitung) | 
                            • <strong>Debit:</strong> Akun <code>2003 - PPN Masukan</code> (jika ada) | 
                            • <strong>Kredit:</strong> Akun <code>2001 - Hutang Usaha (AP)</code>
                        @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('procurement.invoices.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Simpan & Posting Tagihan AP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const itemsData = @json($items);
    let rowCount = 0;

    function formatRupiah(num) {
        return 'Rp ' + Number(Math.round(num)).toLocaleString('id-ID');
    }

    function setTaxRate(val) {
        document.getElementById('taxRateInput').value = val;
        recomputeInvoice();
    }

    function addRow(selectedItemId = '', qty = 1, price = 0) {
        const tbody = document.getElementById('itemsTableBody');
        if (!tbody) return;

        let optionsHtml = '<option value="">-- Pilih Barang --</option>';
        itemsData.forEach(item => {
            const isSel = (item.id == selectedItemId) ? 'selected' : '';
            const unitName = item.unit ? item.unit.name : 'Unit';
            optionsHtml += `<option value="${item.id}" data-price="${item.purchase_price}" ${isSel}>${item.code} - ${item.name} (${unitName})</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = `row_${rowCount}`;
        tr.innerHTML = `
            <td class="ps-4">
                <select name="items[${rowCount}][item_id]" class="form-select form-select-solid form-select-sm item-select" required onchange="onItemChange(this, ${rowCount})">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="number" step="0.01" min="0.01" name="items[${rowCount}][quantity]" class="form-control form-control-solid form-control-sm text-center qty-calc" value="${qty}" required oninput="recomputeInvoice()">
            </td>
            <td>
                <input type="text" name="items[${rowCount}][koli]" class="form-control form-control-solid form-control-sm text-center" placeholder="e.g. 5 Koli">
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowCount}][unit_price]" class="form-control form-control-solid form-control-sm text-end price-calc" id="price_${rowCount}" value="${price}" required oninput="recomputeInvoice()">
            </td>
            <td>
                <input type="text" class="form-control form-control-solid form-control-sm text-end fw-bold line-total" id="total_${rowCount}" readonly value="Rp 0">
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-icon btn-sm btn-light-danger" onclick="removeRow(${rowCount})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        if (window.$ && $.fn.select2) {
            $(tr).find('.item-select').select2({ width: '100%' });
        }

        rowCount++;
        recomputeInvoice();
    }

    function handleNewItemAdded(item) {
        itemsData.push({
            id: item.id,
            code: item.code,
            name: item.name,
            purchase_price: item.purchase_price,
            selling_price: item.selling_price
        });
        document.querySelectorAll('select[name^="items["][name$="[item_id]"]').forEach(select => {
            const opt = new Option(`${item.code} - ${item.name}`, item.id);
            opt.setAttribute('data-price', item.purchase_price);
            select.add(opt);
        });
        addRow(item.id, 1, item.purchase_price);
    }

    function removeRow(id) {
        const tr = document.getElementById(`row_${id}`);
        if (tr) {
            tr.remove();
            recomputeInvoice();
        }
    }

    function onItemChange(selectEl, idx) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const defaultPrice = selectedOption ? (selectedOption.getAttribute('data-price') || 0) : 0;
        const priceInput = document.getElementById(`price_${idx}`);
        if (priceInput) {
            priceInput.value = defaultPrice;
        }
        recomputeInvoice();
    }

    function recomputeInvoice() {
        let subtotal = 0;
        document.querySelectorAll('tbody tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('.qty-calc')?.value) || 0;
            const p = parseFloat(tr.querySelector('.price-calc')?.value) || 0;
            const line = q * p;
            subtotal += line;
            const totalEl = tr.querySelector('.line-total');
            if (totalEl) totalEl.value = formatRupiah(line);
        });

        const taxRate = parseFloat(document.getElementById('taxRateInput')?.value) || 0;
        const taxAmount = taxRate > 0 ? Math.round(subtotal * (taxRate / 100)) : 0;
        const grandTotal = subtotal + taxAmount;

        const taxAmountInput = document.getElementById('taxAmountInput');
        if (taxAmountInput) taxAmountInput.value = taxAmount;

        const dispSubtotal = document.getElementById('dispSubtotal');
        if (dispSubtotal) dispSubtotal.innerText = formatRupiah(subtotal);

        const dispTaxRate = document.getElementById('dispTaxRate');
        if (dispTaxRate) dispTaxRate.innerText = taxRate;

        const dispTaxAmount = document.getElementById('dispTaxAmount');
        if (dispTaxAmount) dispTaxAmount.innerText = formatRupiah(taxAmount);

        const dispGrandTotal = document.getElementById('dispGrandTotal');
        if (dispGrandTotal) dispGrandTotal.innerText = formatRupiah(grandTotal);
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-calc') || e.target.classList.contains('price-calc') || e.target.id === 'taxRateInput') {
            recomputeInvoice();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        @if(empty($selectedGrn))
            addRow();
        @endif
        recomputeInvoice();
    });
</script>
@endsection
