@extends('layouts.app')

@section('title', 'Edit Bill of Materials (BOM)')
@section('page_title', 'Edit Bill of Materials (BOM)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Edit Resep BOM: {{ $bom->code }}</h2>
            <p class="text-muted fs-7 mb-0">Perbarui formula resep produksi, takaran bahan baku, dan estimasi biaya HPP</p>
        </div>
        <a href="{{ route('manufacturing.boms.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar BOM
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-3"></i> Edit Formula BOM: {{ $bom->code }}
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Perbarui parameter formula resep, takaran bahan baku, serta estimasi biaya per unit
                </span>
            </div>
        </div>

        <form action="{{ route('manufacturing.boms.update', $bom->id) }}" method="POST" id="bomForm">
            @csrf
            @method('PUT')
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas Formula & Produk Jadi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Kode BOM</label>
                        <input type="text" name="code" class="form-control form-control-solid form-control-sm @error('code') is-invalid @enderror" value="{{ old('code', $bom->code) }}" required placeholder="Kode formula..." />
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Nama Formula / Resep</label>
                        <input type="text" name="name" class="form-control form-control-solid form-control-sm @error('name') is-invalid @enderror" value="{{ old('name', $bom->name) }}" placeholder="Contoh: Resep Roti Manis Cokelat..." required />
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label required fs-7 fw-semibold text-gray-700 mb-0">Barang Jadi Target Output</label>
                            <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-bold fs-8" onclick="openQuickItemModal('#bomTargetItemSelect')">
                                <i class="bi bi-plus-circle me-1"></i> Buat Master Barang
                            </button>
                        </div>
                        <select name="item_id" id="bomTargetItemSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Barang Jadi / Paket Hasil Manufaktur --" required>
                            <option value="">-- Pilih Barang Jadi / Paket Hasil Manufaktur --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id', $bom->item_id) == $item->id ? 'selected' : '' }}>
                                    [{{ strtoupper($item->item_type ?? 'ITEM') }}] {{ $item->code }} - {{ $item->name }} (Satuan: {{ $item->unit?->symbol ?? 'Unit' }})
                                </option>
                            @endforeach
                        </select>
                        @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Petunjuk Teknis / Instruksi SOP Pembuatan</label>
                        <textarea name="description" class="form-control form-control-solid form-control-sm" rows="2" placeholder="Catatan panduan langkah pembuatan, takaran suhu, waktu mixing, dll...">{{ old('description', $bom->description) }}</textarea>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Kalkulasi Output & Biaya Konversi Produksi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <label class="form-label required fs-7 fw-semibold text-gray-700">Hasil Output (Yield Quantity)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.0001" min="0.0001" name="yield_quantity" id="yieldInput" class="form-control form-control-solid form-control-sm text-end fw-bold" value="{{ old('yield_quantity', $bom->yield_quantity) }}" required oninput="recalcBomTotals()" />
                            <span class="input-group-text bg-light border-0 text-muted">Unit</span>
                        </div>
                        <span class="text-muted fs-9 d-block mt-1">Estimasi produk jadi per 1 batch resep</span>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Biaya Tenaga Kerja (Labor)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" min="0" name="labor_cost" id="laborCostInput" class="form-control form-control-solid form-control-sm text-end" value="{{ old('labor_cost', $bom->labor_cost) }}" oninput="recalcBomTotals()" />
                        </div>
                        <span class="text-muted fs-9 d-block mt-1">Biaya upah kerja per 1 batch</span>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Biaya Overhead (Mesin/Listrik)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" min="0" name="overhead_cost" id="overheadCostInput" class="form-control form-control-solid form-control-sm text-end" value="{{ old('overhead_cost', $bom->overhead_cost) }}" oninput="recalcBomTotals()" />
                        </div>
                        <span class="text-muted fs-9 d-block mt-1">Biaya utilitas mesin & energi per batch</span>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Status Resep BOM</label>
                        <div class="form-check form-check-solid p-2.5 rounded bg-light border border-gray-200">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="isActiveSwitch" value="1" {{ $bom->is_active ? 'checked' : '' }} />
                            <label class="form-check-label fw-semibold text-gray-800 fs-7" for="isActiveSwitch">
                                Resep Aktif / Siap Pakai
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Daftar Bahan Baku & Komponen (Bill of Items)</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-primary fw-semibold" onclick="openItemLookupForNewRow()">
                            <i class="bi bi-search me-1"></i> Cari Bahan
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addBomRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-5">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="bomItemsTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4" style="min-width: 320px;">Bahan Baku / Komponen <span class="text-danger">*</span></th>
                                <th class="text-center" style="width: 170px;">Takaran / Qty <span class="text-danger">*</span></th>
                                <th class="text-end" style="width: 190px;">Harga Beli Satuan</th>
                                <th class="text-end" style="width: 180px;">Subtotal (Rp)</th>
                                <th class="text-center pe-4" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="bomItemsBody" class="fw-semibold text-gray-700">
                            <!-- Dynamic Rows Injected By JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Section 4 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">4</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Ringkasan Estimasi Biaya & HPP Produksi</h4>
                </div>

                <div class="row g-4 mb-2">
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                                <span class="text-muted">Total Biaya Bahan Baku:</span>
                                <span class="fw-bold text-gray-900" id="lblMaterialCost">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2 fs-7">
                                <span class="text-muted">Biaya Tenaga Kerja (Labor):</span>
                                <span class="fw-bold text-gray-900" id="lblLaborCost">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-0 fs-7">
                                <span class="text-muted">Biaya Overhead (Mesin/Listrik):</span>
                                <span class="fw-bold text-gray-900" id="lblOverheadCost">Rp 0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded-3 p-4 bg-light-primary border-primary border-dashed h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-gray-800 fs-7">Estimasi Total Biaya 1 Batch:</span>
                                <span class="fw-bold text-gray-900 fs-5" id="lblTotalBatchCost">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-primary">
                                <div>
                                    <span class="fw-bold text-primary fs-7 d-block">Est. HPP / Unit Output:</span>
                                    <span class="text-muted fs-8" id="lblYieldInfo">Yield: {{ $bom->yield_quantity }} Unit Output</span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-primary fs-3" id="lblUnitHpp">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('manufacturing.boms.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Perbarui Resep BOM
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lookup Bahan Baku -->
<div class="modal fade" id="itemLookupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="w-35px h-35px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                        <i class="bi bi-box-seam fs-4 text-primary"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-gray-800 m-0">Pilih Bahan Baku / Komponen</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-success fw-bold" onclick="openQuickItemModal()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Master Bahan
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                    <input type="text" id="modalLookupSearch" class="form-control form-control-solid form-control-sm ps-10" placeholder="Cari Kode SKU, Nama Bahan, Barcode..." oninput="filterLookupTable()">
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0" id="lookupTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-3">Kode / SKU</th>
                                <th>Nama Bahan Baku</th>
                                <th>Satuan</th>
                                <th class="text-center">Stok</th>
                                <th class="text-end">Harga Beli Standar</th>
                                <th class="text-end pe-3">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="lookupTableBody">
                            @foreach($materials as $mat)
                            <tr class="lookup-item-row" data-search="{{ strtolower($mat->code . ' ' . $mat->name . ' ' . ($mat->barcode ?? '') . ' ' . ($mat->category->name ?? '')) }}">
                                <td class="ps-3 fw-bold text-gray-900"><code>{{ $mat->code }}</code></td>
                                <td>
                                    <div class="fw-bold text-gray-800">{{ $mat->name }}</div>
                                    <span class="text-muted fs-9">Tipe: {{ strtoupper($mat->item_type ?? 'ITEM') }}</span>
                                </td>
                                <td><span class="badge badge-light-info fs-9">{{ $mat->unit?->symbol ?? 'Unit' }}</span></td>
                                <td class="text-center">
                                    <span class="badge {{ $mat->current_stock > 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-9">
                                        {{ number_format($mat->current_stock, 0) }} {{ $mat->unit?->symbol ?? '' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-gray-900">Rp {{ number_format($mat->purchase_price, 0, ',', '.') }}</td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-primary btn-xs py-1 px-3" onclick="selectItemFromLookup({{ $mat->id }}, '{{ $mat->code }}', '{{ addslashes($mat->name) }}', {{ $mat->purchase_price }}, '{{ $mat->unit?->symbol ?? 'Unit' }}')">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const rawMaterials = @json($materials);
    const materialsData = rawMaterials.map(m => ({
        id: m.id,
        code: m.code || '',
        name: m.name || '',
        barcode: m.barcode || '',
        price: m.purchase_price || 0,
        unit: m.unit ? m.unit.symbol : 'Satuan'
    }));
    const existingItems = @json($bom->items);

    let rowIndex = 0;
    let targetRowElement = null;
    let isCreatingNewRowFromLookup = false;

    function handleBomAutocomplete(inputEl) {
        const query = inputEl.value.trim().toLowerCase();
        const wrapper = inputEl.closest('.position-relative');
        const dropdown = wrapper.querySelector('.autocomplete-dropdown');

        if (query.length < 3) {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            return;
        }

        const filtered = materialsData.filter(item => {
            return (item.code && item.code.toLowerCase().includes(query)) ||
                   (item.name && item.name.toLowerCase().includes(query)) ||
                   (item.barcode && item.barcode.toLowerCase().includes(query));
        }).slice(0, 10);

        if (filtered.length === 0) {
            dropdown.innerHTML = `
                <div class="p-3 text-muted text-center fs-8">
                    <i class="bi bi-info-circle me-1"></i> Tidak ada bahan baku cocok dengan "<b>${query}</b>"
                </div>
            `;
            dropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        filtered.forEach((item, idx) => {
            html += `
                <div class="autocomplete-item px-3 py-2 border-bottom border-gray-100 cursor-pointer d-flex justify-content-between align-items-center ${idx === 0 ? 'bg-light-primary active' : ''}" 
                    data-item-id="${item.id}"
                    data-index="${idx}"
                    onclick="selectBomAutocompleteItem(this, ${item.id})"
                    onmouseenter="highlightBomItem(this)">
                    <div>
                        <div class="fw-bold text-gray-800 fs-7">${item.name}</div>
                        <div class="text-muted fs-8">Kode: <span class="badge badge-light-dark fs-9">${item.code}</span> | Satuan: ${item.unit}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-light-primary fw-bold fs-8">Rp ${item.price.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
    }

    function highlightBomItem(el) {
        const dropdown = el.closest('.autocomplete-dropdown');
        dropdown.querySelectorAll('.autocomplete-item').forEach(i => i.classList.remove('active', 'bg-light-primary'));
        el.classList.add('active', 'bg-light-primary');
    }

    function selectBomAutocompleteItem(element, itemId) {
        const item = materialsData.find(it => it.id == itemId);
        const wrapper = element.closest('.position-relative');
        const hiddenId = wrapper.querySelector('.item-id-input');
        const input = wrapper.querySelector('.item-autocomplete-input');
        const dropdown = wrapper.querySelector('.autocomplete-dropdown');
        const tr = wrapper.closest('tr');
        const rowId = tr.id.replace('bom-row-', '');

        if (item) {
            hiddenId.value = item.id;
            input.value = `[${item.code}] ${item.name}`;

            const costInput = document.getElementById(`cost-input-${rowId}`);
            const unitLabel = document.getElementById(`unit-label-${rowId}`);
            if (costInput) costInput.value = item.price;
            if (unitLabel) unitLabel.innerText = item.unit;
        }

        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';
        calcRowSubtotal(rowId);
    }

    function handleBomAutocompleteKeydown(event, inputEl) {
        const wrapper = inputEl.closest('.position-relative');
        const dropdown = wrapper.querySelector('.autocomplete-dropdown');
        if (dropdown.classList.contains('d-none')) return;

        const items = dropdown.querySelectorAll('.autocomplete-item');
        let activeItem = dropdown.querySelector('.autocomplete-item.active');
        let currentIndex = activeItem ? parseInt(activeItem.getAttribute('data-index')) : -1;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            currentIndex = (currentIndex + 1) < items.length ? currentIndex + 1 : 0;
            items.forEach(el => el.classList.remove('active', 'bg-light-primary'));
            if (items[currentIndex]) {
                items[currentIndex].classList.add('active', 'bg-light-primary');
                items[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            currentIndex = (currentIndex - 1) >= 0 ? currentIndex - 1 : items.length - 1;
            items.forEach(el => el.classList.remove('active', 'bg-light-primary'));
            if (items[currentIndex]) {
                items[currentIndex].classList.add('active', 'bg-light-primary');
                items[currentIndex].scrollIntoView({ block: 'nearest' });
            }
        } else if (event.key === 'Enter') {
            if (activeItem) {
                event.preventDefault();
                const itemId = activeItem.getAttribute('data-item-id');
                selectBomAutocompleteItem(activeItem, itemId);
            } else if (items.length > 0) {
                event.preventDefault();
                const firstItem = items[0];
                const itemId = firstItem.getAttribute('data-item-id');
                selectBomAutocompleteItem(firstItem, itemId);
            }
        } else if (event.key === 'Escape') {
            dropdown.classList.add('d-none');
        }
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.position-relative')) {
            document.querySelectorAll('.autocomplete-dropdown').forEach(d => {
                d.classList.add('d-none');
            });
        }
    });

    function addBomRow(selectedItemId = '', qty = 1, cost = '', unitSymbol = 'Satuan') {
        const tbody = document.getElementById('bomItemsBody');
        const tr = document.createElement('tr');
        const currentRowIndex = rowIndex;
        tr.id = `bom-row-${currentRowIndex}`;

        let itemText = '';
        let itemCost = cost;
        let itemUnit = unitSymbol;

        if (selectedItemId) {
            const currentMat = materialsData.find(m => m.id == selectedItemId);
            if (currentMat) {
                itemText = `[${currentMat.code}] ${currentMat.name}`;
                itemUnit = currentMat.unit ? currentMat.unit : (unitSymbol || 'Satuan');
                if (itemCost === '' || itemCost === null) itemCost = currentMat.price || 0;
            }
        }

        tr.innerHTML = `
            <td class="ps-4">
                <div class="position-relative">
                    <input type="hidden" name="items[${currentRowIndex}][item_id]" class="item-id-input" value="${selectedItemId}" required>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-solid form-control-sm item-autocomplete-input" 
                            placeholder="Ketik min. 3 karakter kode / nama bahan..." 
                            value="${itemText}" 
                            autocomplete="off"
                            oninput="handleBomAutocomplete(this)" 
                            onkeydown="handleBomAutocompleteKeydown(event, this)"
                            onfocus="handleBomAutocomplete(this)">
                        <button type="button" class="btn btn-icon btn-light-primary btn-sm flex-shrink-0" onclick="openItemLookupForRow(this)" title="Pilih Bahan dari Katalog">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="autocomplete-dropdown position-absolute bg-white border rounded shadow-lg w-100 z-index-3 py-1 mt-1 d-none" style="max-height: 280px; overflow-y: auto; z-index: 1050; left: 0; top: 100%;"></div>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.0001" min="0.0001" name="items[${currentRowIndex}][quantity]" id="qty-input-${currentRowIndex}" class="form-control form-control-solid form-control-sm text-center qty-input" value="${qty}" required oninput="calcRowSubtotal(${currentRowIndex})" />
                    <span class="input-group-text bg-light border-0 text-muted unit-label fs-8" id="unit-label-${currentRowIndex}">${itemUnit}</span>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-0 text-muted fs-8">Rp</span>
                    <input type="number" step="0.01" min="0" name="items[${currentRowIndex}][unit_cost]" id="cost-input-${currentRowIndex}" class="form-control form-control-solid form-control-sm text-end cost-input" value="${itemCost}" placeholder="0" oninput="calcRowSubtotal(${currentRowIndex})" />
                </div>
            </td>
            <td class="text-end fw-bold text-gray-800" id="subtotal-cell-${currentRowIndex}">
                Rp 0
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-icon btn-light-danger btn-sm" onclick="removeBomRow(${currentRowIndex})" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
        calcRowSubtotal(currentRowIndex);
        rowIndex++;
        return tr;
    }

    function removeBomRow(idx) {
        const row = document.getElementById(`bom-row-${idx}`);
        const tbody = document.getElementById('bomItemsBody');
        if (tbody.children.length > 1) {
            if (row) {
                row.remove();
                recalcBomTotals();
            }
        } else {
            alert('Minimal harus ada 1 baris bahan baku.');
        }
    }

    function calcRowSubtotal(idx) {
        const qty = parseFloat(document.getElementById(`qty-input-${idx}`)?.value) || 0;
        const cost = parseFloat(document.getElementById(`cost-input-${idx}`)?.value) || 0;
        const subtotal = qty * cost;
        
        const cell = document.getElementById(`subtotal-cell-${idx}`);
        if (cell) {
            cell.innerText = 'Rp ' + Math.round(subtotal).toLocaleString('id-ID');
        }

        recalcBomTotals();
    }

    function recalcBomTotals() {
        let totalMaterial = 0;
        const rows = document.querySelectorAll('#bomItemsBody tr');
        
        rows.forEach(tr => {
            const id = tr.id.replace('bom-row-', '');
            const qty = parseFloat(document.getElementById(`qty-input-${id}`)?.value) || 0;
            const cost = parseFloat(document.getElementById(`cost-input-${id}`)?.value) || 0;
            totalMaterial += (qty * cost);
        });

        const laborCost = parseFloat(document.getElementById('laborCostInput')?.value) || 0;
        const overheadCost = parseFloat(document.getElementById('overheadCostInput')?.value) || 0;
        const yieldQty = parseFloat(document.getElementById('yieldInput')?.value) || 1;

        const totalBatch = totalMaterial + laborCost + overheadCost;
        const unitHpp = yieldQty > 0 ? (totalBatch / yieldQty) : 0;

        document.getElementById('lblMaterialCost').innerText = 'Rp ' + Math.round(totalMaterial).toLocaleString('id-ID');
        document.getElementById('lblLaborCost').innerText = 'Rp ' + Math.round(laborCost).toLocaleString('id-ID');
        document.getElementById('lblOverheadCost').innerText = 'Rp ' + Math.round(overheadCost).toLocaleString('id-ID');
        document.getElementById('lblTotalBatchCost').innerText = 'Rp ' + Math.round(totalBatch).toLocaleString('id-ID');
        document.getElementById('lblUnitHpp').innerText = 'Rp ' + Math.round(unitHpp).toLocaleString('id-ID');
        document.getElementById('lblYieldInfo').innerText = `Yield: ${yieldQty} Unit Output`;
    }

    function openItemLookupForRow(btnOrInput) {
        targetRowElement = btnOrInput.closest('tr');
        isCreatingNewRowFromLookup = false;
        document.getElementById('modalLookupSearch').value = '';
        filterLookupTable();
        new bootstrap.Modal(document.getElementById('itemLookupModal')).show();
    }

    function openItemLookupForNewRow() {
        targetRowElement = null;
        isCreatingNewRowFromLookup = true;
        document.getElementById('modalLookupSearch').value = '';
        filterLookupTable();
        new bootstrap.Modal(document.getElementById('itemLookupModal')).show();
    }

    function selectItemFromLookup(id, code, name, price, unit) {
        if (isCreatingNewRowFromLookup) {
            addBomRow(id, 1, price, unit);
        } else if (targetRowElement) {
            const hiddenId = targetRowElement.querySelector('.item-id-input');
            const input = targetRowElement.querySelector('.item-autocomplete-input');
            const rowId = targetRowElement.id.replace('bom-row-', '');
            const costInput = document.getElementById(`cost-input-${rowId}`);
            const unitLabel = document.getElementById(`unit-label-${rowId}`);

            if (hiddenId) hiddenId.value = id;
            if (input) input.value = `[${code}] ${name}`;
            if (costInput) costInput.value = price;
            if (unitLabel) unitLabel.innerText = unit;

            calcRowSubtotal(rowId);
        }

        const modalEl = document.getElementById('itemLookupModal');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }

    function filterLookupTable() {
        const query = document.getElementById('modalLookupSearch').value.toLowerCase().trim();
        document.querySelectorAll('.lookup-item-row').forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            if (searchData.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (existingItems && existingItems.length > 0) {
            existingItems.forEach(item => {
                const mat = item.item || {};
                const unitSymbol = (mat.unit ? mat.unit.symbol : null) || (item.unit ? item.unit.symbol : 'Satuan');
                addBomRow(item.item_id, item.quantity, item.unit_cost, unitSymbol);
            });
        } else if (materialsData && materialsData.length > 0) {
            const first = materialsData[0];
            addBomRow(first.id, 1, first.price, first.unit);
        } else {
            addBomRow();
        }
    });
</script>
@endsection
