@extends('layouts.app')

@section('title', 'Catat Penerimaan Barang')
@section('page_title', 'Formulir Penerimaan Barang (Goods Receipt Note)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Catat Penerimaan Barang (GRN)</h2>
            <p class="text-muted fs-7 mb-0">Verifikasi fisik barang masuk ke gudang dari PO atau penerimaan langsung (Direct GRN tanpa PO)</p>
        </div>
        <a href="{{ route('procurement.grn.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penerimaan
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-box-arrow-in-down text-primary fs-3"></i> Formulir Penerimaan Barang (Goods Receipt Note)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    @if($selectedPo)
                        Penerimaan mengacu pada PO: <span class="badge badge-light-primary fw-bold">{{ $selectedPo->po_number }}</span>
                    @else
                        Mode Penerimaan Langsung (Direct GRN / Bypass tanpa PO)
                    @endif
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('procurement.grn.store') }}" id="grnForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Dokumen Penerimaan & Sumber Barang</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Rujukan Purchase Order (PO)</label>
                        <select name="purchase_order_id" class="form-select form-select-solid form-select-sm" onchange="window.location.href='{{ route('procurement.grn.create') }}' + (this.value ? '?po_id=' + this.value : '');">
                            <option value="">-- Penerimaan Langsung (Tanpa PO / Direct) --</option>
                            @foreach($openOrders as $oPo)
                                <option value="{{ $oPo->id }}" {{ $selectedPo && $selectedPo->id == $oPo->id ? 'selected' : '' }}>
                                    {{ $oPo->po_number }} - {{ $oPo->supplier->name }} (Rp {{ number_format($oPo->grand_total, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-muted fs-8">Pilih PO jika ada order, atau biarkan kosong untuk bypass langsung tanpa PO</span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Supplier / Pemasok</label>
                        @if($selectedPo)
                            <input type="hidden" name="supplier_id" value="{{ $selectedPo->supplier_id }}">
                            <input type="text" class="form-control form-control-solid form-control-sm fw-bold text-gray-800" value="{{ $selectedPo->supplier->code }} - {{ $selectedPo->supplier->name }}" readonly>
                        @else
                            <select name="supplier_id" id="supplier_id" class="form-select form-select-solid form-select-sm" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->code }} - {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Gudang Tujuan Masuk</label>
                        <select name="warehouse_id" class="form-select form-select-solid form-select-sm" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Penerimaan Fisik</label>
                        <input type="date" name="receipt_date" class="form-control form-control-solid form-control-sm" value="{{ old('receipt_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Surat Jalan Supplier</label>
                        <input type="text" name="delivery_note_number" class="form-control form-control-solid form-control-sm" placeholder="Contoh: SJ/2026/09/8821" value="{{ old('delivery_note_number') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan Penerimaan</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Kondisi fisik, segel truk, suhu kontainer..." value="{{ old('notes') }}">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">
                            Rincian Barang yang Diterima
                            @if($selectedPo)
                                <span class="text-muted fs-7 fw-normal ms-1">(Berdasarkan PO: <strong>{{ $selectedPo->po_number }}</strong>)</span>
                            @else
                                <span class="badge badge-light-success fs-8 fw-semibold ms-2">Direct Input / Dual-Unit (Koli & Kg)</span>
                            @endif
                        </h4>
                    </div>

                    @if(!$selectedPo)
                    <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addRow()">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baris Barang
                    </button>
                    @endif
                </div>

                @if($selectedPo)
                <!-- Mode 1: Goods Receipt based on PO -->
                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3">Kode & Nama Barang</th>
                                <th class="text-center">Total Dipesan</th>
                                <th class="text-center" style="width: 140px;">Koli (Ket)</th>
                                <th class="text-center">Sudah Diterima</th>
                                <th class="text-center">Sisa Belum Tiba</th>
                                <th class="text-end pe-4" style="width: 200px;">Jumlah Diterima Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-700">
                            @foreach($selectedPo->items as $idx => $pItem)
                            @if($pItem->pending_quantity > 0)
                            <tr>
                                <td class="ps-4">
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $pItem->item_id }}">
                                    <span class="text-gray-900 fw-bold">{{ $pItem->item->code }}</span> - {{ $pItem->item->name }}
                                </td>
                                <td class="text-center">{{ number_format($pItem->quantity, 0) }} {{ $pItem->item->unit->symbol }}</td>
                                <td>
                                    <input type="text" name="items[{{ $idx }}][koli]" class="form-control form-control-solid form-control-sm text-center" value="{{ $pItem->koli }}" placeholder="Contoh: 2 Bal">
                                </td>
                                <td class="text-center">{{ number_format($pItem->received_quantity, 0) }}</td>
                                <td class="text-center"><span class="badge badge-light-warning fw-bold">{{ number_format($pItem->pending_quantity, 0) }}</span></td>
                                <td class="pe-4">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm text-end" value="{{ $pItem->pending_quantity }}" max="{{ $pItem->pending_quantity }}" min="0.01" required>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <!-- Mode 2: Direct Goods Receipt without PO -->
                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="directGrnTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="min-width: 250px;">Pilih Barang / Material</th>
                                <th class="text-center" style="width: 140px;">Koli (Kemasan)</th>
                                <th class="text-center" style="width: 160px;">Qty Diterima (Kg/Unit)</th>
                                <th class="text-end" style="width: 180px;">Estimasi HPP (Rp)</th>
                                <th class="text-end" style="width: 180px;">Total Nilai (Rp)</th>
                                <th class="text-center pe-4" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody" class="fw-semibold text-gray-700">
                            <!-- Dynamic rows injected here -->
                        </tbody>
                        <tfoot>
                            <tr class="bg-light fw-bold">
                                <td colspan="4" class="text-end ps-4 py-3 text-muted">Estimasi Total Nilai Penerimaan:</td>
                                <td class="text-end text-primary fs-6 py-3" id="totalGrnDisplay">Rp 0</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                <div class="alert alert-dismissible bg-light-success d-flex align-items-center p-4 mb-2 border border-success border-dashed rounded-3">
                    <i class="bi bi-info-circle-fill fs-2 text-success me-3"></i>
                    <div class="d-flex flex-column text-gray-800 fs-7">
                        <strong>Catatan Otomasi Inventori & Akuntansi:</strong>
                        <span>
                            Penyimpanan formulir penerimaan ini langsung menambah saldo stok fisik (Kg) dan Koli pada gudang terpilih, serta membentuk Jurnal Akuntansi otomatis:
                            <strong>Debit:</strong> Akun Persediaan (1201) &bull; 
                            <strong>Kredit:</strong> Akun Penerimaan Barang Belum Ditagih / Unbilled GRN (1301/2001).
                            Faktur Pembelian (AP) dapat dibuat kapan saja dengan memilih referensi GRN ini.
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('procurement.grn.index') }}" class="btn btn-sm btn-light fw-semibold">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-semibold px-5" id="submitBtn">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Konfirmasi & Posting Penerimaan Barang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const itemsData = @json($items ?? []);
    let rowCount = 0;

    function formatRupiah(num) {
        return 'Rp ' + Number(Math.round(num)).toLocaleString('id-ID');
    }

    function addRow(selectedItemId = '', qty = 1, koli = '', unitCost = 0) {
        const tbody = document.getElementById('itemsTableBody');
        if (!tbody) return;

        let optionsHtml = '<option value="">-- Pilih Barang --</option>';
        itemsData.forEach(item => {
            const isSel = (item.id == selectedItemId) ? 'selected' : '';
            const unitSymbol = item.unit ? item.unit.symbol : 'Unit';
            optionsHtml += `<option value="${item.id}" data-price="${item.purchase_price}" data-unit="${unitSymbol}" ${isSel}>${item.code} - ${item.name} (${unitSymbol})</option>`;
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
                <input type="text" name="items[${rowCount}][koli]" class="form-control form-control-solid form-control-sm text-center" value="${koli}" placeholder="Contoh: 2 Bal">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" min="0.01" name="items[${rowCount}][quantity]" class="form-control form-control-solid form-control-sm text-end qty-calc" value="${qty}" required oninput="recomputeDirectGRN()">
                    <span class="input-group-text bg-light border-0 text-muted unit-badge" id="unit_badge_${rowCount}">Kg</span>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${rowCount}][unit_cost]" class="form-control form-control-solid form-control-sm text-end cost-calc" id="cost_${rowCount}" value="${unitCost}" required oninput="recomputeDirectGRN()">
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

        if (selectedItemId) {
            const selEl = tr.querySelector('.item-select');
            onItemChange(selEl, rowCount);
        }

        rowCount++;
        recomputeDirectGRN();
    }

    function removeRow(id) {
        const tr = document.getElementById(`row_${id}`);
        if (tr) {
            tr.remove();
            recomputeDirectGRN();
        }
    }

    function onItemChange(selectEl, idx) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const defaultPrice = selectedOption ? (selectedOption.getAttribute('data-price') || 0) : 0;
        const unitSymbol = selectedOption ? (selectedOption.getAttribute('data-unit') || 'Unit') : 'Unit';

        const costInput = document.getElementById(`cost_${idx}`);
        if (costInput && (!costInput.value || parseFloat(costInput.value) === 0)) {
            costInput.value = defaultPrice;
        }

        const unitBadge = document.getElementById(`unit_badge_${idx}`);
        if (unitBadge) {
            unitBadge.innerText = unitSymbol;
        }

        recomputeDirectGRN();
    }

    function recomputeDirectGRN() {
        let total = 0;
        document.querySelectorAll('#itemsTableBody tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('.qty-calc')?.value) || 0;
            const c = parseFloat(tr.querySelector('.cost-calc')?.value) || 0;
            const line = q * c;
            total += line;
            const totalEl = tr.querySelector('.line-total');
            if (totalEl) totalEl.value = formatRupiah(line);
        });

        const totalDisplay = document.getElementById('totalGrnDisplay');
        if (totalDisplay) {
            totalDisplay.innerText = formatRupiah(total);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(!$selectedPo)
            addRow();
        @endif
    });
</script>
@endsection
