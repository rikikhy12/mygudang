@extends('layouts.app')

@section('title', 'Buat Sales Order')
@section('page_title', 'Formulir Sales Order (Pesanan Penjualan)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Buat Sales Order Baru</h2>
            <p class="text-muted fs-7 mb-0">Catat pesanan penjualan resmi dari pelanggan</p>
        </div>
        <a href="{{ route('sales.so.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar SO
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-bag-plus-fill text-primary fs-3"></i> Formulir Pemesanan Sales Order
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi data pelanggan, tanggal pemesanan, estimasi pengiriman, dan rincian item barang
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('sales.so.store') }}" id="soForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Pemesanan & Pelanggan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required mb-0">Pelanggan / Customer</label>
                            <button type="button" class="btn btn-xs btn-link text-success p-0 fw-bold fs-8" onclick="openQuickContactModal('customer', '#customer_id')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Customer
                            </button>
                        </div>
                        <select name="customer_id" id="customer_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Customer --" required>
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Pesanan</label>
                        <input type="date" name="order_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Estimasi Tanggal Pengiriman</label>
                        <input type="date" name="expected_delivery_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Keterangan Pesanan</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Instruksi pengiriman khusus, referensi PO pelanggan, lokasi proyek...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rincian Barang & Layanan Jasa</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light-primary btn-sm fw-semibold" onclick="openItemLookupForNewRow()">
                            <i class="bi bi-search me-1"></i> Cari Barang (Lookup)
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="addSoRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-5">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="soTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="min-width: 280px;">Barang / Layanan Jasa</th>
                                <th class="text-center" style="width: 110px;">Jumlah (Qty)</th>
                                <th class="text-center" style="width: 120px;">Koli (Ket)</th>
                                <th class="text-end" style="width: 180px;">Harga Jual Satuan (Rp)</th>
                                <th class="text-end" style="width: 180px;">Subtotal</th>
                                <th class="text-center pe-4" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="soBody" class="fw-semibold text-gray-700">
                            @php 
                                $firstItem = $items->first(); 
                                $firstMinPrice = $firstItem ? $firstItem->getMinimumSellingPrice() : 0;
                                $firstMargin = $firstItem ? $firstItem->getEffectiveMinMarginPercent() : 0;
                                $firstIsService = ($firstItem && !$firstItem->isPhysical()) ? 1 : 0;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="position-relative">
                                        <input type="hidden" name="items[0][item_id]" class="item-id-input" value="{{ $firstItem->id ?? '' }}" 
                                            data-code="{{ $firstItem->code ?? '' }}" 
                                            data-name="{{ $firstItem->name ?? '' }}" 
                                            data-min-price="{{ $firstMinPrice }}" 
                                            data-margin="{{ $firstMargin }}" 
                                            data-is-service="{{ $firstIsService }}" 
                                            data-price="{{ $firstItem->selling_price ?? 0 }}"
                                            required>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-solid form-control-sm item-autocomplete-input" 
                                                placeholder="Ketik min. 3 karakter kode / nama barang..." 
                                                value="{{ $firstItem ? '[' . $firstItem->code . '] ' . $firstItem->name : '' }}" 
                                                autocomplete="off"
                                                oninput="handleItemAutocomplete(this)" 
                                                onkeydown="handleAutocompleteKeydown(event, this)"
                                                onfocus="handleItemAutocomplete(this)">
                                            <button type="button" class="btn btn-icon btn-light-primary btn-sm flex-shrink-0" onclick="openItemLookupForRow(this)" title="Buka Katalog Visual (Popup)">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                        <div class="autocomplete-dropdown position-absolute bg-white border rounded shadow-lg w-100 z-index-3 py-1 mt-1 d-none" style="max-height: 280px; overflow-y: auto; z-index: 1050; left: 0; top: 100%;"></div>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-solid form-control-sm qty-input text-center" value="1" min="0.01" required oninput="calcTotals()">
                                </td>
                                <td>
                                    <input type="text" name="items[0][koli]" class="form-control form-control-solid form-control-sm text-center" placeholder="e.g. 5 Koli">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-solid form-control-sm price-input text-end" value="{{ $firstItem->selling_price ?? 0 }}" min="0" required oninput="calcTotals()">
                                    <div class="margin-warning-box"></div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-solid form-control-sm line-total text-end fw-bold text-gray-800" readonly value="Rp {{ number_format($firstItem->selling_price ?? 0, 0, ',', '.') }}">
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeSoRow(this)" title="Hapus Baris">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Kalkulasi Total & Pajak / Diskon</h4>
                </div>

                <div class="row justify-content-end mb-2">
                    <div class="col-lg-5 col-md-7">
                        <div class="card bg-light border border-dashed border-gray-300 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold fs-7">Subtotal Pesanan:</span>
                                <span class="text-gray-900 fw-bolder fs-6" id="displaySubtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold fs-7">Potongan Diskon (Rp):</span>
                                <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-solid form-control-sm text-end w-140px" value="0" oninput="calcTotals()">
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold fs-7">Tarif PPN (%):</span>
                                <div class="input-group input-group-sm w-140px">
                                    <input type="number" step="0.01" name="tax_rate" id="taxRateInput" class="form-control form-control-solid form-control-sm text-end" value="0" oninput="calcTotals()">
                                    <span class="input-group-text bg-light border-0 text-muted">%</span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted fw-bold fs-7">Nilai PPN:</span>
                                <span class="text-gray-900 fw-bold fs-7" id="displayTaxAmount">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top border-gray-400">
                                <span class="text-gray-900 fw-bolder fs-5">Grand Total SO:</span>
                                <span class="text-primary fw-bolder fs-4" id="displayGrandTotal">Rp 0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('sales.so.index') }}" class="btn btn-sm btn-light fw-semibold">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-semibold px-5">
                    <i class="bi bi-check2-circle me-1"></i> Terbitkan Sales Order
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Lookup Barang -->
<div class="modal fade" id="itemLookupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-3 bg-light d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold text-gray-800 m-0">
                    <i class="bi bi-box-seam text-primary me-2"></i> Pilih Barang / Jasa dari Katalog
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light-success fw-bold" onclick="openQuickItemModal()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Barang Baru
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                    <input type="text" id="modalLookupSearch" class="form-control form-control-solid form-control-sm ps-10" placeholder="Ketik Kode SKU, Nama Barang, atau Barcode..." oninput="filterLookupTable()">
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 no-datatable" id="lookupTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-3">Kode / SKU</th>
                                <th>Nama Barang / Jasa</th>
                                <th>Kategori</th>
                                <th class="text-center">Stok</th>
                                <th class="text-end">Harga Jual Standar</th>
                                <th class="text-end pe-3 no-sort">Pilih</th>
                            </tr>
                        </thead>
                        <tbody id="lookupTableBody">
                            @foreach($items as $it)
                            <tr class="lookup-item-row" data-search="{{ strtolower($it->code . ' ' . $it->name . ' ' . ($it->barcode ?? '') . ' ' . ($it->category->name ?? '')) }}">
                                <td class="ps-3 fw-bold text-gray-900"><code>{{ $it->code }}</code></td>
                                <td>
                                    <div class="fw-bold text-gray-800">{{ $it->name }}</div>
                                    @if($it->barcode)
                                        <div class="text-muted fs-9"><i class="bi bi-upc-scan me-1"></i>{{ $it->barcode }}</div>
                                    @endif
                                </td>
                                <td><span class="badge badge-light-secondary fs-9">{{ $it->category->name ?? 'Umum' }}</span></td>
                                <td class="text-center">
                                    <span class="badge {{ $it->current_stock > 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-9">
                                        {{ number_format($it->current_stock, 0) }} {{ $it->unit->symbol ?? '' }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-gray-900">Rp {{ number_format($it->selling_price, 0, ',', '.') }}</td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-primary btn-xs py-1 px-3" onclick="selectItemFromLookup({{ $it->id }})">
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
<style>
    .autocomplete-dropdown {
        background: #ffffff;
        border: 1px solid #e4e6ef;
        border-radius: 0.475rem;
        box-shadow: 0 10px 30px 0 rgba(82, 63, 105, 0.15);
        z-index: 1050;
    }
    .autocomplete-item {
        transition: background-color 0.15s ease;
    }
    .autocomplete-item:hover, .autocomplete-item.active {
        background-color: #f1faff !important;
    }
</style>
<script>
    let soIndex = 1;
    let targetRowElement = null;
    let isCreatingNewRowFromLookup = false;

    const itemsData = [
        @foreach($items as $it)
        {
            id: {{ $it->id }},
            code: "{{ addslashes($it->code) }}",
            name: "{{ addslashes($it->name) }}",
            price: {{ (float)($it->selling_price ?? 0) }},
            minPrice: {{ (float)$it->getMinimumSellingPrice() }},
            margin: {{ (float)$it->getEffectiveMinMarginPercent() }},
            isService: {{ !$it->isPhysical() ? 1 : 0 }},
            unit: "{{ addslashes($it->unit->name ?? 'PCS') }}",
            stock: {{ (float)($it->current_stock ?? 0) }},
            barcode: "{{ addslashes($it->barcode ?? '') }}"
        },
        @endforeach
    ];

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function calcTotals() {
        let subtotal = 0;

        document.querySelectorAll('#soBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty-input')?.value) || 0;
            const priceInput = tr.querySelector('.price-input');
            const price = parseFloat(priceInput?.value) || 0;
            const line = qty * price;
            const lineTotalEl = tr.querySelector('.line-total');
            if (lineTotalEl) lineTotalEl.value = formatRupiah(line);
            subtotal += line;

            // Margin check
            const hiddenId = tr.querySelector('.item-id-input');
            const isService = hiddenId ? parseInt(hiddenId.getAttribute('data-is-service') || 0) : 0;
            const minPrice = hiddenId ? parseFloat(hiddenId.getAttribute('data-min-price') || 0) : 0;
            const margin = hiddenId ? parseFloat(hiddenId.getAttribute('data-margin') || 0) : 0;
            const warningBox = tr.querySelector('.margin-warning-box');

            if (!isService && minPrice > 0 && price < minPrice) {
                if (priceInput) priceInput.classList.add('border-danger', 'text-danger');
                if (warningBox) {
                    warningBox.innerHTML = `<span class="badge badge-light-danger fs-9 mt-1 py-1" title="Harga di bawah limit margin"><i class="bi bi-exclamation-triangle-fill me-1"></i>Min Rp ${Math.round(minPrice).toLocaleString('id-ID')} (${margin}%)</span>`;
                }
            } else {
                if (priceInput) priceInput.classList.remove('border-danger', 'text-danger');
                if (warningBox) warningBox.innerHTML = '';
            }
        });

        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const taxRate = parseFloat(document.getElementById('taxRateInput')?.value) || 0;
        const taxable = Math.max(0, subtotal - discount);
        const taxAmount = taxable * (taxRate / 100);
        const grandTotal = taxable + taxAmount;

        const subtotalEl = document.getElementById('displaySubtotal');
        const taxEl = document.getElementById('displayTaxAmount');
        const grandEl = document.getElementById('displayGrandTotal');

        if (subtotalEl) subtotalEl.innerText = formatRupiah(subtotal);
        if (taxEl) taxEl.innerText = formatRupiah(taxAmount);
        if (grandEl) grandEl.innerText = formatRupiah(grandTotal);
    }

    function handleItemAutocomplete(inputEl) {
        const wrapper = inputEl.closest('.position-relative');
        const dropdown = wrapper.querySelector('.autocomplete-dropdown');
        const query = inputEl.value.trim().toLowerCase();

        // Only trigger suggestions if input has at least 3 characters
        if (query.length < 3) {
            dropdown.classList.add('d-none');
            dropdown.innerHTML = '';
            return;
        }

        const matches = itemsData.filter(item => {
            return item.code.toLowerCase().includes(query) || 
                   item.name.toLowerCase().includes(query) || 
                   (item.barcode && item.barcode.toLowerCase().includes(query));
        });

        if (matches.length === 0) {
            dropdown.innerHTML = `
                <div class="px-3 py-2 text-muted fs-8">
                    <i class="bi bi-info-circle me-1"></i> Tidak ada barang yang cocok dengan "<b>${escapeHtml(inputEl.value)}</b>"
                </div>
            `;
            dropdown.classList.remove('d-none');
            return;
        }

        let html = '';
        matches.slice(0, 15).forEach((item, index) => {
            html += `
                <div class="autocomplete-item px-3 py-2 cursor-pointer border-bottom d-flex align-items-center justify-content-between hover-bg-light"
                     data-item-id="${item.id}"
                     data-index="${index}"
                     onmousedown="selectAutocompleteItem(this, ${item.id})">
                    <div class="d-flex flex-column">
                        <div>
                            <span class="badge badge-light-primary fw-bold fs-9 me-1">${escapeHtml(item.code)}</span>
                            <span class="fw-bold text-gray-800 fs-7">${escapeHtml(item.name)}</span>
                        </div>
                        <div class="text-muted fs-9">
                            <span class="me-2">Satuan: <b class="text-gray-700">${escapeHtml(item.unit)}</b></span>
                            <span class="me-2">Stok: <b class="${item.stock > 0 ? 'text-success' : 'text-danger'}">${item.stock}</b></span>
                        </div>
                    </div>
                    <div class="text-end ps-2">
                        <div class="fw-bolder text-primary fs-7">Rp ${item.price.toLocaleString('id-ID')}</div>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
    }

    function selectAutocompleteItem(elem, itemId) {
        const wrapper = elem.closest('.position-relative');
        const tr = elem.closest('tr');
        const input = wrapper.querySelector('.item-autocomplete-input');
        const hiddenId = wrapper.querySelector('.item-id-input');
        const dropdown = wrapper.querySelector('.autocomplete-dropdown');

        const item = itemsData.find(it => it.id == itemId);
        if (item) {
            hiddenId.value = item.id;
            hiddenId.setAttribute('data-code', item.code);
            hiddenId.setAttribute('data-name', item.name);
            hiddenId.setAttribute('data-price', item.price);
            hiddenId.setAttribute('data-min-price', item.minPrice || 0);
            hiddenId.setAttribute('data-margin', item.margin || 0);
            hiddenId.setAttribute('data-is-service', item.isService || 0);
            input.value = `[${item.code}] ${item.name}`;

            const priceInput = tr.querySelector('.price-input');
            if (priceInput) {
                priceInput.value = item.price;
            }
        }

        dropdown.classList.add('d-none');
        dropdown.innerHTML = '';
        calcTotals();
    }

    function handleAutocompleteKeydown(event, inputEl) {
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
                selectAutocompleteItem(activeItem, itemId);
            } else if (items.length > 0) {
                event.preventDefault();
                const firstItem = items[0];
                const itemId = firstItem.getAttribute('data-item-id');
                selectAutocompleteItem(firstItem, itemId);
            }
        } else if (event.key === 'Escape') {
            dropdown.classList.add('d-none');
        }
    }

    function addSoRow(selectedId = '') {
        const tbody = document.getElementById('soBody');
        const tr = document.createElement('tr');
        
        let itemText = '';
        let itemPrice = 0;
        let minPrice = 0;
        let margin = 0;
        let isService = 0;
        let itemName = '';
        let itemCode = '';

        if (selectedId) {
            const item = itemsData.find(it => it.id == selectedId);
            if (item) {
                itemText = `[${item.code}] ${item.name}`;
                itemPrice = item.price;
                minPrice = item.minPrice;
                margin = item.margin;
                isService = item.isService;
                itemName = item.name;
                itemCode = item.code;
            }
        }

        tr.innerHTML = `
            <td class="ps-4">
                <div class="position-relative">
                    <input type="hidden" name="items[${soIndex}][item_id]" class="item-id-input" value="${selectedId}" 
                        data-code="${itemCode}" 
                        data-name="${itemName}" 
                        data-min-price="${minPrice}" 
                        data-margin="${margin}" 
                        data-is-service="${isService}" 
                        data-price="${itemPrice}"
                        required>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-solid form-control-sm item-autocomplete-input" 
                            placeholder="Ketik min. 3 karakter kode / nama barang..." 
                            value="${itemText}" 
                            autocomplete="off"
                            oninput="handleItemAutocomplete(this)" 
                            onkeydown="handleAutocompleteKeydown(event, this)"
                            onfocus="handleItemAutocomplete(this)">
                        <button type="button" class="btn btn-icon btn-light-primary btn-sm flex-shrink-0" onclick="openItemLookupForRow(this)" title="Buka Katalog Visual (Popup)">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="autocomplete-dropdown position-absolute bg-white border rounded shadow-lg w-100 z-index-3 py-1 mt-1 d-none" style="max-height: 280px; overflow-y: auto; z-index: 1050; left: 0; top: 100%;"></div>
                </div>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${soIndex}][quantity]" class="form-control form-control-solid form-control-sm qty-input text-center" value="1" min="0.01" required oninput="calcTotals()">
            </td>
            <td>
                <input type="text" name="items[${soIndex}][koli]" class="form-control form-control-solid form-control-sm text-center" placeholder="e.g. 5 Koli">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${soIndex}][unit_price]" class="form-control form-control-solid form-control-sm price-input text-end" value="${itemPrice}" min="0" required oninput="calcTotals()">
                <div class="margin-warning-box"></div>
            </td>
            <td>
                <input type="text" class="form-control form-control-solid form-control-sm line-total text-end fw-bold text-gray-800" readonly value="${formatRupiah(itemPrice)}">
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeSoRow(this)" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);

        soIndex++;
        calcTotals();
        return tr;
    }

    function removeSoRow(btn) {
        const tbody = document.getElementById('soBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcTotals();
        } else {
            alert('Minimal harus ada 1 baris barang.');
        }
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

    function selectItemFromLookup(id) {
        const item = itemsData.find(it => it.id == id);
        if (!item) return;

        if (isCreatingNewRowFromLookup) {
            addSoRow(id);
        } else if (targetRowElement) {
            const hiddenId = targetRowElement.querySelector('.item-id-input');
            const input = targetRowElement.querySelector('.item-autocomplete-input');
            const priceInput = targetRowElement.querySelector('.price-input');

            if (hiddenId) {
                hiddenId.value = item.id;
                hiddenId.setAttribute('data-code', item.code);
                hiddenId.setAttribute('data-name', item.name);
                hiddenId.setAttribute('data-price', item.price);
                hiddenId.setAttribute('data-min-price', item.minPrice || 0);
                hiddenId.setAttribute('data-margin', item.margin || 0);
                hiddenId.setAttribute('data-is-service', item.isService || 0);
            }
            if (input) input.value = `[${item.code}] ${item.name}`;
            if (priceInput) priceInput.value = item.price;
            calcTotals();
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

    document.getElementById('soForm').addEventListener('submit', function(e) {
        let violations = [];
        document.querySelectorAll('#soBody tr').forEach(tr => {
            const hiddenId = tr.querySelector('.item-id-input');
            if (!hiddenId || !hiddenId.value) return;

            const isService = parseInt(hiddenId.getAttribute('data-is-service') || 0);
            const minPrice = parseFloat(hiddenId.getAttribute('data-min-price') || 0);
            const margin = parseFloat(hiddenId.getAttribute('data-margin') || 0);
            const name = hiddenId.getAttribute('data-name') || 'Barang';
            const price = parseFloat(tr.querySelector('.price-input')?.value) || 0;

            if (!isService && minPrice > 0 && price < minPrice) {
                violations.push(`- ${name}: Harga Rp ${price.toLocaleString('id-ID')} (Minimum: Rp ${Math.round(minPrice).toLocaleString('id-ID')} / Margin ${margin}%)`);
            }
        });

        if (violations.length > 0) {
            e.preventDefault();
            alert("PERHATIAN: Transaksi tidak dapat disimpan karena harga jual di bawah batas minimum margin:\n\n" + violations.join("\n"));
            return false;
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.position-relative')) {
            document.querySelectorAll('.autocomplete-dropdown').forEach(d => d.classList.add('d-none'));
        }
    });

    document.addEventListener('DOMContentLoaded', calcTotals);
</script>
@endsection
