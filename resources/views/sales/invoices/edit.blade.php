@extends('layouts.app')

@section('title', 'Edit Faktur Penjualan')
@section('page_title', 'Formulir Edit Faktur Penjualan (Invoice AR)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Faktur Penjualan: {{ $invoice->invoice_number }}</h2>
            <p class="text-muted fs-7 mb-0">Perbarui rincian kuantitas, harga, PPN, dan penyesuaian jurnal piutang usaha</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('sales.invoices.show', $invoice->id) }}" class="btn btn-light-secondary btn-sm fw-semibold">
                <i class="bi bi-eye me-1"></i> Lihat Faktur
            </a>
            <a href="{{ route('sales.invoices.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Faktur
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger d-flex align-items-center p-4 mb-0 rounded-3">
        <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-3"></i>
        <div>
            <h5 class="mb-1 text-danger fw-bold">Gagal Menyimpan Perubahan Faktur:</h5>
            <ul class="mb-0 ps-3 fs-7">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Faktur Penjualan: {{ $invoice->invoice_number }}
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui data pelanggan, tanggal jatuh tempo, tarif pajak PPN, dan kuantitas faktur
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('sales.invoices.update', $invoice->id) }}" id="sinvEditForm">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Pelanggan & Parameter Faktur</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Faktur</label>
                        <input type="text" class="form-control form-control-solid form-control-sm fw-bold" value="{{ $invoice->invoice_number }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Pelanggan / Customer</label>
                        <select name="customer_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ $invoice->customer_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->code }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Faktur</label>
                        <input type="date" name="invoice_date" class="form-control form-control-solid form-control-sm" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" class="form-control form-control-solid form-control-sm" value="{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+14 days')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Pajak PPN (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.1" min="0" max="100" name="tax_rate" id="taxRateInput" class="form-control form-control-solid form-control-sm text-end" value="{{ $invoice->tax_rate ?? ($invoice->subtotal > 0 ? round(($invoice->tax_amount / $invoice->subtotal) * 100, 1) : 0) }}" oninput="recomputeInvoice()">
                            <span class="input-group-text bg-light border-0 text-muted">%</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="setTaxRate(0)">0%</button>
                            <button type="button" class="btn btn-outline-primary btn-sm px-2" onclick="setTaxRate(11)">11%</button>
                            <button type="button" class="btn btn-outline-primary btn-sm px-2" onclick="setTaxRate(12)">12%</button>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nominal Pajak PPN (Rp)</label>
                        <input type="number" step="0.01" name="tax_amount" id="taxAmountInput" class="form-control form-control-solid form-control-sm text-end" value="{{ $invoice->tax_amount }}" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan Tambahan</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Instruksi pembayaran / catatan faktur..." value="{{ $invoice->notes }}">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">
                            Rincian Item Barang / Jasa
                            @if($invoice->deliveryOrder)
                                <span class="badge badge-light-info fw-semibold fs-8 ms-2">
                                    Surat Jalan: {{ $invoice->deliveryOrder->do_number }}
                                </span>
                            @endif
                        </h4>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="itemsTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3">Kode & Nama Barang</th>
                                <th class="text-center" style="width: 140px;">Qty Faktur</th>
                                <th class="text-end" style="width: 200px;">Harga Jual Satuan</th>
                                <th class="text-end pe-4" style="width: 200px;">Subtotal Baris</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            @foreach($invoice->items as $idx => $invItem)
                            @php 
                                $line = $invItem->quantity * $invItem->unit_price; 
                                $canChange = (bool) ($invItem->item?->allow_change_price ?? false);
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $invItem->item_id }}">
                                    <span class="text-gray-900 fw-bold">{{ $invItem->item->code }}</span> - {{ $invItem->item->name }}
                                    @if($invItem->item && $invItem->item->isService())
                                        <span class="badge badge-light-warning fs-9 ms-1">Jasa</span>
                                    @endif
                                    @if($canChange)
                                        <span class="badge badge-light-success fs-9 ms-1" title="Bisa Ubah Harga"><i class="bi bi-pencil-fill text-success" style="font-size: 8px;"></i> Bebas</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm qty-calc text-center" value="{{ $invItem->quantity }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" class="form-control form-control-solid form-control-sm price-calc text-end {{ !$canChange ? 'bg-light text-muted' : 'border-success' }}" value="{{ $invItem->unit_price }}" required {{ !$canChange ? 'readonly' : '' }} title="{{ $canChange ? 'Harga jual dapat diketik/diubah' : 'Harga jual terkunci master' }}">
                                </td>
                                <td class="pe-4">
                                    <input type="text" class="form-control form-control-solid form-control-sm line-total text-end fw-bold text-gray-900" readonly value="Rp {{ number_format($line, 0, ',', '.') }}">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Ringkasan Total Tagihan & Piutang</h4>
                </div>

                <div class="row justify-content-end mb-4">
                    <div class="col-md-5">
                        <div class="bg-light p-4 rounded-3 border border-gray-300">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Subtotal Penjualan:</span>
                                <span class="text-gray-900 fw-bold fs-7" id="dispSubtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted fs-7 fw-semibold">Pajak PPN (<span id="dispTaxRate">0</span>%):</span>
                                <span class="text-gray-800 fw-bold fs-7" id="dispTaxAmount">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-400">
                                <span class="text-gray-900 fw-bolder fs-6">Grand Total Faktur (AR):</span>
                                <span class="text-primary fw-bolder fs-4" id="dispGrandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning d-flex align-items-center p-4 mb-2 rounded-3 fs-7">
                    <i class="bi bi-info-circle fs-2 text-warning me-3"></i>
                    <div class="d-flex flex-column text-gray-800">
                        <strong>Sinkronisasi Jurnal Akuntansi:</strong>
                        <span>Menyimpan perubahan faktur akan secara otomatis memperbarui jurnal Piutang Usaha (1101), Pendapatan Penjualan, dan PPN Keluaran.</span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('sales.invoices.show', $invoice->id) }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Perubahan Faktur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function formatRupiah(num) {
        return 'Rp ' + Number(Math.round(num)).toLocaleString('id-ID');
    }

    function setTaxRate(val) {
        document.getElementById('taxRateInput').value = val;
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
        recomputeInvoice();
    });
</script>
@endsection
