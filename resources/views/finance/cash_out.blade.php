@extends('layouts.app')

@section('title', 'Kas Keluar (Payment Voucher)')
@section('page_title', 'Pengeluaran Dana Kas & Bank (Cash Out)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Kas Keluar (Payment Voucher)</h2>
            <p class="text-muted fs-7 mb-0">Catat pengeluaran kas atau transfer bank untuk pembayaran hutang, biaya operasional, atau pembelian tunai</p>
        </div>
        <a href="{{ route('finance.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mutasi Kas & Bank
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-up-right-circle-fill text-danger fs-3"></i> Formulir Pengeluaran Kas / Bank
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi rekening sumber pembayaran, tanggal, penerima dana, dan pos rincian beban/hutang
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.cash-out.store') }}" id="cashOutForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rekening Sumber & Informasi Pengeluaran</h4>
                    </div>
                    <!-- Mode Switcher -->
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="payment_mode_radio" id="modeSingle" value="single" checked onchange="setPaymentMode('single')">
                        <label class="btn btn-outline btn-outline-primary btn-sm fw-bold py-1 px-3" for="modeSingle">
                            <i class="bi bi-wallet2 me-1"></i> Rekening Tunggal
                        </label>
                        <input type="radio" class="btn-check" name="payment_mode_radio" id="modeSplit" value="split" onchange="setPaymentMode('split')">
                        <label class="btn btn-outline btn-outline-info btn-sm fw-bold py-1 px-3" for="modeSplit">
                            <i class="bi bi-diagram-2 me-1"></i> Split Multi-Rekening (Pecah Kas/Bank)
                        </label>
                    </div>
                    <input type="hidden" name="payment_mode" id="paymentModeInput" value="single">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Pembayaran</label>
                        <input type="date" name="transaction_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required />
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-0">Dibayarkan Kepada (Kontak)</label>
                            <button type="button" class="btn btn-xs btn-link text-success p-0 fw-bold fs-8" onclick="openQuickContactModal('supplier', '#cashOutContactSelect')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Kontak
                            </button>
                        </div>
                        <select name="contact_id" id="cashOutContactSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Non-Kontak / Penerima Beban --">
                            <option value="">-- Non-Kontak / Penerima Beban --</option>
                            @foreach($contacts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Single Account Mode Box -->
                <div id="singleAccountSection" class="p-3 bg-light rounded border border-gray-300 mb-5">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fs-7 fw-bold text-gray-800 required mb-0">Sumber Rekening Kas / Bank Pembayar</label>
                                <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-bold fs-8" onclick="openQuickCoaModal('#cashOutAccountSelect')">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Akun
                                </button>
                            </div>
                            <select name="account_id" id="cashOutAccountSelect" class="form-select form-select-solid form-select-sm">
                                @foreach($cashAccounts as $ca)
                                    <option value="{{ $ca->id }}">{{ $ca->code }} - {{ $ca->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-9 mt-1">Seluruh total tagihan/pengeluaran akan ditarik dari rekening ini.</div>
                        </div>
                    </div>
                </div>

                <!-- Split Multi-Account Mode Box -->
                <div id="splitAccountSection" class="p-3 bg-light-info rounded border border-info border-dashed mb-5 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-gray-900 m-0 fs-7 d-flex align-items-center gap-1">
                                <i class="bi bi-diagram-2 text-info fs-5"></i> Rincian Pecah Sumber Dana (Multi Kas & Bank)
                            </h5>
                            <span class="text-muted fs-9">Tentukan rekening mana saja yang digunakan untuk membayar beserta nominal masing-masing.</span>
                        </div>
                        <button type="button" class="btn btn-xs btn-primary fw-bold" onclick="addSourceRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Sumber Kas/Bank
                        </button>
                    </div>

                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-2 fs-7 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th style="width: 45%;">Rekening Kas / Bank Sumber</th>
                                    <th style="width: 30%;">Nominal Bayar (Rp)</th>
                                    <th style="width: 20%;">Catatan / Ref</th>
                                    <th class="text-center" style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="splitSourceBody">
                                <tr class="split-source-row">
                                    <td>
                                        <select name="source_accounts[0][account_id]" class="form-select form-select-solid form-select-sm source-acc-select">
                                            @foreach($cashAccounts as $ca)
                                                <option value="{{ $ca->id }}">{{ $ca->code }} - {{ $ca->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" step="0.01" min="0" name="source_accounts[0][amount]" class="form-control form-control-solid form-control-sm source-amount text-end fw-bold" value="0" oninput="calcSourceTotal()">
                                            <button type="button" class="btn btn-xs btn-link text-primary fw-bold p-0 text-nowrap" onclick="fillSourceRemaining(this)" title="Isi sisa kekurangan">
                                                <i class="bi bi-magic"></i> Sisa
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="source_accounts[0][notes]" class="form-control form-control-solid form-control-sm" placeholder="Ref / Memo..." />
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-icon btn-light-danger btn-xs" onclick="removeSourceRow(this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Split Balance Status Bar -->
                    <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top fs-8">
                        <div>
                            <span class="text-muted">Total Sumber Dana Terkumpul:</span>
                            <strong class="text-info fs-7 ms-1" id="displaySourceTotal">Rp 0</strong>
                        </div>
                        <div id="splitDiffStatusBadge" class="badge badge-light-danger fw-bolder fs-8">
                            Belum Seimbang
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Referensi & Keterangan Pengeluaran</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        @php
                            $apAccount = $contraAccounts->firstWhere('code', '2001') ?? $contraAccounts->first(fn($a) => str_contains(strtolower($a->name), 'hutang') || str_contains(strtolower($a->name), 'payable'));
                        @endphp
                        <label class="form-label fs-7 fw-semibold text-gray-700">Pelunasan Faktur Pembelian / AP (Opsional)</label>
                        <select name="purchase_invoice_id" id="purchaseInvSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Bukan Pelunasan Faktur Tertentu --" onchange="autoFillInvoice(this)">
                            <option value="">-- Bukan Pelunasan Faktur Tertentu --</option>
                            @foreach($unpaidPurchaseInvoices as $inv)
                                <option value="{{ $inv->id }}" 
                                    data-amount="{{ $inv->remaining_balance }}" 
                                    data-supplier-id="{{ $inv->supplier_id }}" 
                                    data-supplier="{{ $inv->supplier->name ?? 'Supplier' }}" 
                                    data-invno="{{ $inv->invoice_number }}"
                                    {{ (isset($selectedInvoiceId) && $selectedInvoiceId == $inv->id) ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} - {{ $inv->supplier->name ?? 'Supplier' }} (Sisa Hutang: Rp {{ number_format($inv->remaining_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">No. Referensi / Bukti Transfer Luar</label>
                        <input type="text" name="reference_number" class="form-control form-control-solid form-control-sm" placeholder="Nomor bukti pembayaran / kuitansi..." />
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi / Keterangan Umum Transaksi</label>
                        <input type="text" name="description" id="cashOutDesc" class="form-control form-control-solid form-control-sm" placeholder="Pengeluaran dana untuk pembayaran..." />
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rincian Pos Akun Beban / Alokasi Pengeluaran (Debit)</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light-warning btn-sm fw-semibold" onclick="openQuickCoaModal('.contra-account-select')">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Akun COA
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="addOutRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="outTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="width: 45%;">Akun Buku Besar (Debit / Beban)</th>
                                <th style="width: 25%;">Nominal Pembayaran (Rp)</th>
                                <th style="width: 25%;">Memo / Keterangan</th>
                                <th class="text-center pe-4" style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="outBody" class="fw-semibold text-gray-700">
                            <tr>
                                <td class="ps-4">
                                    <select name="items[0][account_id]" class="form-select form-select-solid form-select-sm contra-account-select" required>
                                        <option value="">-- Pilih Akun Alokasi Beban --</option>
                                        @foreach($contraAccounts as $coa)
                                            <option value="{{ $coa->id }}" {{ $coa->code == '5101' ? 'selected' : '' }}>
                                                {{ $coa->code }} - {{ $coa->name }} ({{ ucfirst($coa->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][amount]" class="form-control form-control-solid form-control-sm out-amount text-end" value="0" min="1" required oninput="calcOutTotal()" />
                                </td>
                                <td>
                                    <input type="text" name="items[0][notes]" class="form-control form-control-solid form-control-sm" placeholder="Memo pos beban..." />
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeOutRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <div class="bg-light-danger border border-danger border-dashed rounded p-3 text-end" style="min-width: 320px;">
                        <span class="text-danger fw-semibold fs-7 d-block mb-1">Total Alokasi Kas Keluar:</span>
                        <span class="text-danger fw-bold fs-3" id="displayOutTotal">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('finance.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-danger" id="btnSubmitCashOut">
                    <i class="bi bi-check2-circle me-1"></i> Posting Kas Keluar & Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const cashAccountsList = @json($cashAccounts);
    let outIndex = 1;
    let sourceIndex = 1;
    let currentPaymentMode = 'single';

    function setPaymentMode(mode) {
        currentPaymentMode = mode;
        document.getElementById('paymentModeInput').value = mode;
        const singleSec = document.getElementById('singleAccountSection');
        const splitSec = document.getElementById('splitAccountSection');
        const singleSelect = document.getElementById('cashOutAccountSelect');

        if (mode === 'split') {
            singleSec.classList.add('d-none');
            splitSec.classList.remove('d-none');
            if (singleSelect) {
                singleSelect.removeAttribute('required');
                singleSelect.disabled = true;
            }
            document.querySelectorAll('#splitAccountSection input, #splitAccountSection select').forEach(el => el.disabled = false);
            
            // Auto populate initial split row with total out amount if 0
            const totalOut = getOutTotal();
            const firstSourceInput = document.querySelector('#splitSourceBody .source-amount');
            if (firstSourceInput && parseFloat(firstSourceInput.value || 0) === 0 && totalOut > 0) {
                firstSourceInput.value = totalOut;
            }
            calcSourceTotal();
        } else {
            singleSec.classList.remove('d-none');
            splitSec.classList.add('d-none');
            if (singleSelect) {
                singleSelect.disabled = false;
                singleSelect.setAttribute('required', 'required');
            }
            document.querySelectorAll('#splitAccountSection input, #splitAccountSection select').forEach(el => el.disabled = true);
        }
    }

    function addSourceRow() {
        const tbody = document.getElementById('splitSourceBody');
        let optionsHtml = '';
        cashAccountsList.forEach(ca => {
            optionsHtml += `<option value="${ca.id}">${ca.code} - ${ca.name}</option>`;
        });

        const tr = document.createElement('tr');
        tr.className = 'split-source-row';
        tr.innerHTML = `
            <td>
                <select name="source_accounts[${sourceIndex}][account_id]" class="form-select form-select-solid form-select-sm source-acc-select">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    <input type="number" step="0.01" min="0" name="source_accounts[${sourceIndex}][amount]" class="form-control form-control-solid form-control-sm source-amount text-end fw-bold" value="0" oninput="calcSourceTotal()">
                    <button type="button" class="btn btn-xs btn-link text-primary fw-bold p-0 text-nowrap" onclick="fillSourceRemaining(this)" title="Isi sisa kekurangan">
                        <i class="bi bi-magic"></i> Sisa
                    </button>
                </div>
            </td>
            <td>
                <input type="text" name="source_accounts[${sourceIndex}][notes]" class="form-control form-control-solid form-control-sm" placeholder="Ref / Memo..." />
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-light-danger btn-xs" onclick="removeSourceRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        sourceIndex++;
        calcSourceTotal();
    }

    function removeSourceRow(btn) {
        const tbody = document.getElementById('splitSourceBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcSourceTotal();
        } else {
            alert('Minimal harus ada 1 baris rekening sumber.');
        }
    }

    function fillSourceRemaining(btn) {
        const currentRow = btn.closest('tr');
        const totalOut = getOutTotal();
        let otherSourceTotal = 0;

        document.querySelectorAll('#splitSourceBody .split-source-row').forEach(row => {
            if (row !== currentRow) {
                otherSourceTotal += parseFloat(row.querySelector('.source-amount').value || 0);
            }
        });

        const remaining = Math.max(0, totalOut - otherSourceTotal);
        currentRow.querySelector('.source-amount').value = remaining;
        calcSourceTotal();
    }

    function getOutTotal() {
        let total = 0;
        document.querySelectorAll('.out-amount').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        return total;
    }

    function calcSourceTotal() {
        let sourceTotal = 0;
        document.querySelectorAll('#splitSourceBody .source-amount').forEach(el => {
            sourceTotal += parseFloat(el.value) || 0;
        });

        const totalOut = getOutTotal();
        document.getElementById('displaySourceTotal').innerText = 'Rp ' + Number(sourceTotal).toLocaleString('id-ID');

        const badge = document.getElementById('splitDiffStatusBadge');
        if (!badge) return;

        if (Math.abs(sourceTotal - totalOut) < 0.01 && totalOut > 0) {
            badge.className = 'badge badge-light-success fw-bolder fs-8';
            badge.innerText = 'Seimbang (Pas)';
        } else if (sourceTotal < totalOut) {
            badge.className = 'badge badge-light-danger fw-bolder fs-8';
            badge.innerText = `Kurang Rp ${Number(totalOut - sourceTotal).toLocaleString('id-ID')}`;
        } else {
            badge.className = 'badge badge-light-warning fw-bolder fs-8';
            badge.innerText = `Lebih Rp ${Number(sourceTotal - totalOut).toLocaleString('id-ID')}`;
        }
    }

    function addOutRow() {
        const tbody = document.getElementById('outBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4">
                <select name="items[${outIndex}][account_id]" class="form-select form-select-solid form-select-sm contra-account-select" required>
                    <option value="">-- Pilih Akun Alokasi Beban --</option>
                    @foreach($contraAccounts as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${outIndex}][amount]" class="form-control form-control-solid form-control-sm out-amount text-end" value="0" min="1" required oninput="calcOutTotal()">
            </td>
            <td>
                <input type="text" name="items[${outIndex}][notes]" class="form-control form-control-solid form-control-sm" placeholder="Memo pos beban...">
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeOutRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        outIndex++;
    }

    function removeOutRow(btn) {
        const tbody = document.getElementById('outBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcOutTotal();
        } else {
            alert('Minimal 1 baris akun.');
        }
    }

    function calcOutTotal() {
        const total = getOutTotal();
        document.getElementById('displayOutTotal').innerText = 'Rp ' + Number(total).toLocaleString('id-ID');
        if (currentPaymentMode === 'split') {
            calcSourceTotal();
        }
    }

    function autoFillInvoice(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const amount = opt.getAttribute('data-amount');
            const supp = opt.getAttribute('data-supplier');
            const suppId = opt.getAttribute('data-supplier-id');
            const inv = opt.getAttribute('data-invno');

            // 1. Auto-select Supplier Contact
            if (suppId) {
                const contactSelect = document.getElementById('cashOutContactSelect');
                if (contactSelect) {
                    contactSelect.value = suppId;
                    if (window.jQuery && $(contactSelect).data('select2')) {
                        $(contactSelect).trigger('change');
                    }
                }
            }

            // 2. Auto-fill Nominal (Amount)
            const firstAmount = document.querySelector('.out-amount');
            if (firstAmount) {
                firstAmount.value = amount;
            }

            // 3. Auto-select Hutang Usaha (2001) Account in first debit row
            const apAccountId = "{{ $apAccount->id ?? '' }}";
            const firstCoa = document.querySelector('.contra-account-select');
            if (firstCoa && apAccountId) {
                firstCoa.value = apAccountId;
                if (window.jQuery && $(firstCoa).data('select2')) {
                    $(firstCoa).trigger('change');
                }
            }

            // 4. Auto-fill Description & Memo
            const desc = 'Pelunasan Faktur Tagihan AP ' + inv + ' ke ' + supp;
            document.getElementById('cashOutDesc').value = desc;
            const firstNotes = document.querySelector('input[name="items[0][notes]"]');
            if (firstNotes) {
                firstNotes.value = 'Pelunasan Hutang ' + inv;
            }

            calcOutTotal();
        }
    }

    // Form submit validation
    document.getElementById('cashOutForm').addEventListener('submit', function(e) {
        if (currentPaymentMode === 'split') {
            let sourceTotal = 0;
            document.querySelectorAll('#splitSourceBody .source-amount').forEach(el => {
                sourceTotal += parseFloat(el.value) || 0;
            });
            const outTotal = getOutTotal();

            if (Math.abs(sourceTotal - outTotal) > 0.01) {
                e.preventDefault();
                alert(`Total rekening sumber dana (Rp ${Number(sourceTotal).toLocaleString('id-ID')}) belum sama dengan Total Alokasi Pengeluaran (Rp ${Number(outTotal).toLocaleString('id-ID')}). Silakan seimbangkan nominal sebelum posting.`);
                return false;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        setPaymentMode('single');
        const invSelect = document.getElementById('purchaseInvSelect');
        if (invSelect && invSelect.value) {
            autoFillInvoice(invSelect);
        }
    });
</script>
@endsection
