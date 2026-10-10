@extends('layouts.app')

@section('title', 'Kas Masuk (Receipt Voucher)')
@section('page_title', 'Penerimaan Kas & Bank (Cash In)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Kas Masuk (Receipt Voucher)</h2>
            <p class="text-muted fs-7 mb-0">Catat penerimaan kas atau transfer bank dari pelanggan atau pendapatan lainnya</p>
        </div>
        <a href="{{ route('finance.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mutasi Kas & Bank
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-down-left-circle-fill text-success fs-3"></i> Formulir Penerimaan Dana Kas / Bank
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi rekening penerima, tanggal, mitra pembayaran, dan akun pos pendapatan
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.cash-in.store') }}" id="cashInForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rekening Tujuan & Informasi Transaksi</h4>
                    </div>
                    <!-- Mode Switcher -->
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="payment_mode_radio" id="modeSingleIn" value="single" checked onchange="setPaymentModeIn('single')">
                        <label class="btn btn-outline btn-outline-success btn-sm fw-bold py-1 px-3" for="modeSingleIn">
                            <i class="bi bi-wallet2 me-1"></i> Rekening Tunggal
                        </label>
                        <input type="radio" class="btn-check" name="payment_mode_radio" id="modeSplitIn" value="split" onchange="setPaymentModeIn('split')">
                        <label class="btn btn-outline btn-outline-info btn-sm fw-bold py-1 px-3" for="modeSplitIn">
                            <i class="bi bi-diagram-2 me-1"></i> Split Multi-Rekening (Pecah Kas/Bank)
                        </label>
                    </div>
                    <input type="hidden" name="payment_mode" id="paymentModeInputIn" value="single">
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Penerimaan</label>
                        <input type="date" name="transaction_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required />
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-7 fw-semibold text-gray-700 mb-0">Diterima Dari (Kontak)</label>
                            <button type="button" class="btn btn-xs btn-link text-success p-0 fw-bold fs-8" onclick="openQuickContactModal('customer', '#cashInContactSelect')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Kontak
                            </button>
                        </div>
                        <select name="contact_id" id="cashInContactSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Non-Kontak / Umum --">
                            <option value="">-- Non-Kontak / Umum --</option>
                            @foreach($contacts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ ucfirst($c->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Single Account Mode Box -->
                <div id="singleAccountSectionIn" class="p-3 bg-light rounded border border-gray-300 mb-5">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fs-7 fw-bold text-gray-800 required mb-0">Setor ke Rekening Kas / Bank Penampung</label>
                                <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-bold fs-8" onclick="openQuickCoaModal('#cashInAccountSelect')">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Akun
                                </button>
                            </div>
                            <select name="account_id" id="cashInAccountSelect" class="form-select form-select-solid form-select-sm">
                                @foreach($cashAccounts as $ca)
                                    <option value="{{ $ca->id }}">{{ $ca->code }} - {{ $ca->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-muted fs-9 mt-1">Seluruh nominal kas masuk akan ditampung ke rekening ini.</div>
                        </div>
                    </div>
                </div>

                <!-- Split Multi-Account Mode Box -->
                <div id="splitAccountSectionIn" class="p-3 bg-light-info rounded border border-info border-dashed mb-5 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-gray-900 m-0 fs-7 d-flex align-items-center gap-1">
                                <i class="bi bi-diagram-2 text-info fs-5"></i> Rincian Pecah Penampung Dana (Multi Kas & Bank)
                            </h5>
                            <span class="text-muted fs-9">Tentukan rekening penampung beserta alokasi nominal masing-masing kas/bank.</span>
                        </div>
                        <button type="button" class="btn btn-xs btn-primary fw-bold" onclick="addTargetRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Rekening Penampung
                        </button>
                    </div>

                    <div class="table-responsive bg-white rounded border">
                        <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-2 fs-7 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                    <th style="width: 45%;">Rekening Kas / Bank Penampung</th>
                                    <th style="width: 30%;">Nominal Diterima (Rp)</th>
                                    <th style="width: 20%;">Catatan / Ref</th>
                                    <th class="text-center" style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="splitTargetBody">
                                <tr class="split-target-row">
                                    <td>
                                        <select name="target_accounts[0][account_id]" class="form-select form-select-solid form-select-sm target-acc-select">
                                            @foreach($cashAccounts as $ca)
                                                <option value="{{ $ca->id }}">{{ $ca->code }} - {{ $ca->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" step="0.01" min="0" name="target_accounts[0][amount]" class="form-control form-control-solid form-control-sm target-amount text-end fw-bold" value="0" oninput="calcTargetTotal()">
                                            <button type="button" class="btn btn-xs btn-link text-primary fw-bold p-0 text-nowrap" onclick="fillTargetRemaining(this)" title="Isi sisa kekurangan">
                                                <i class="bi bi-magic"></i> Sisa
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="target_accounts[0][notes]" class="form-control form-control-solid form-control-sm" placeholder="Ref / Memo..." />
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-icon btn-light-danger btn-xs" onclick="removeTargetRow(this)">
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
                            <span class="text-muted">Total Dana Diterima ke Kas/Bank:</span>
                            <strong class="text-success fs-7 ms-1" id="displayTargetTotal">Rp 0</strong>
                        </div>
                        <div id="splitDiffStatusBadgeIn" class="badge badge-light-danger fw-bolder fs-8">
                            Belum Seimbang
                        </div>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Referensi & Keterangan Transaksi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        @php
                            $arAccount = $contraAccounts->firstWhere('code', '1102') ?? $contraAccounts->first(fn($a) => str_contains(strtolower($a->name), 'piutang') || str_contains(strtolower($a->name), 'receivable'));
                        @endphp
                        <label class="form-label fs-7 fw-semibold text-gray-700">Pelunasan Faktur Penjualan / AR (Opsional)</label>
                        <select name="sales_invoice_id" id="salesInvSelect" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Bukan Pelunasan Faktur Tertentu --" onchange="autoFillInvoice(this)">
                            <option value="">-- Bukan Pelunasan Faktur Tertentu --</option>
                            @foreach($unpaidSalesInvoices as $inv)
                                <option value="{{ $inv->id }}" 
                                    data-amount="{{ $inv->remaining_balance }}" 
                                    data-customer-id="{{ $inv->customer_id }}" 
                                    data-customer="{{ $inv->customer->name ?? 'Pelanggan' }}" 
                                    data-invno="{{ $inv->invoice_number }}"
                                    {{ (isset($selectedInvoiceId) && $selectedInvoiceId == $inv->id) ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} - {{ $inv->customer->name ?? 'Pelanggan' }} (Sisa Piutang: Rp {{ number_format($inv->remaining_balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">No. Referensi / Bukti Transfer Luar</label>
                        <input type="text" name="reference_number" class="form-control form-control-solid form-control-sm" placeholder="Nomor bukti transfer / kuitansi..." />
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Deskripsi / Keterangan Umum Transaksi</label>
                        <input type="text" name="description" id="cashInDesc" class="form-control form-control-solid form-control-sm" placeholder="Penerimaan pembayaran dari..." />
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Rincian Pos Akun Sumber Penerimaan (Kredit)</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light-warning btn-sm fw-semibold" onclick="openQuickCoaModal('.contra-account-select')">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Akun COA
                        </button>
                        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="addInRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="inTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="width: 45%;">Akun Buku Besar (Credit / Sumber)</th>
                                <th style="width: 25%;">Nominal Penerimaan (Rp)</th>
                                <th style="width: 25%;">Memo / Keterangan</th>
                                <th class="text-center pe-4" style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="inBody" class="fw-semibold text-gray-700">
                            <tr>
                                <td class="ps-4">
                                    <select name="items[0][account_id]" class="form-select form-select-solid form-select-sm contra-account-select" required>
                                        <option value="">-- Pilih Akun Sumber --</option>
                                        @foreach($contraAccounts as $coa)
                                            <option value="{{ $coa->id }}" {{ $coa->code == '1101' ? 'selected' : '' }}>
                                                {{ $coa->code }} - {{ $coa->name }} ({{ ucfirst($coa->type) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][amount]" class="form-control form-control-solid form-control-sm in-amount text-end" value="0" min="1" required oninput="calcInTotal()" />
                                </td>
                                <td>
                                    <input type="text" name="items[0][notes]" class="form-control form-control-solid form-control-sm" placeholder="Memo pos penerimaan..." />
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeInRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <div class="bg-light-success border border-success border-dashed rounded p-3 text-end" style="min-width: 320px;">
                        <span class="text-success fw-semibold fs-7 d-block mb-1">Total Alokasi Kas Masuk:</span>
                        <span class="text-success fw-bold fs-3" id="displayInTotal">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('finance.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-success" id="btnSubmitCashIn">
                    <i class="bi bi-check2-circle me-1"></i> Posting Kas Masuk & Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const cashAccountsList = @json($cashAccounts);
    let inIndex = 1;
    let targetIndex = 1;
    let currentPaymentModeIn = 'single';

    function setPaymentModeIn(mode) {
        currentPaymentModeIn = mode;
        document.getElementById('paymentModeInputIn').value = mode;
        const singleSec = document.getElementById('singleAccountSectionIn');
        const splitSec = document.getElementById('splitAccountSectionIn');
        const singleSelect = document.getElementById('cashInAccountSelect');

        if (mode === 'split') {
            singleSec.classList.add('d-none');
            splitSec.classList.remove('d-none');
            if (singleSelect) {
                singleSelect.removeAttribute('required');
                singleSelect.disabled = true;
            }
            document.querySelectorAll('#splitAccountSectionIn input, #splitAccountSectionIn select').forEach(el => el.disabled = false);
            
            // Auto populate initial split row with total in amount if 0
            const totalIn = getInTotal();
            const firstTargetInput = document.querySelector('#splitTargetBody .target-amount');
            if (firstTargetInput && parseFloat(firstTargetInput.value || 0) === 0 && totalIn > 0) {
                firstTargetInput.value = totalIn;
            }
            calcTargetTotal();
        } else {
            singleSec.classList.remove('d-none');
            splitSec.classList.add('d-none');
            if (singleSelect) {
                singleSelect.disabled = false;
                singleSelect.setAttribute('required', 'required');
            }
            document.querySelectorAll('#splitAccountSectionIn input, #splitAccountSectionIn select').forEach(el => el.disabled = true);
        }
    }

    function addTargetRow() {
        const tbody = document.getElementById('splitTargetBody');
        let optionsHtml = '';
        cashAccountsList.forEach(ca => {
            optionsHtml += `<option value="${ca.id}">${ca.code} - ${ca.name}</option>`;
        });

        const tr = document.createElement('tr');
        tr.className = 'split-target-row';
        tr.innerHTML = `
            <td>
                <select name="target_accounts[${targetIndex}][account_id]" class="form-select form-select-solid form-select-sm target-acc-select">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    <input type="number" step="0.01" min="0" name="target_accounts[${targetIndex}][amount]" class="form-control form-control-solid form-control-sm target-amount text-end fw-bold" value="0" oninput="calcTargetTotal()">
                    <button type="button" class="btn btn-xs btn-link text-primary fw-bold p-0 text-nowrap" onclick="fillTargetRemaining(this)" title="Isi sisa kekurangan">
                        <i class="bi bi-magic"></i> Sisa
                    </button>
                </div>
            </td>
            <td>
                <input type="text" name="target_accounts[${targetIndex}][notes]" class="form-control form-control-solid form-control-sm" placeholder="Ref / Memo..." />
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-icon btn-light-danger btn-xs" onclick="removeTargetRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        targetIndex++;
        calcTargetTotal();
    }

    function removeTargetRow(btn) {
        const tbody = document.getElementById('splitTargetBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcTargetTotal();
        } else {
            alert('Minimal harus ada 1 baris rekening penampung.');
        }
    }

    function fillTargetRemaining(btn) {
        const currentRow = btn.closest('tr');
        const totalIn = getInTotal();
        let otherTargetTotal = 0;

        document.querySelectorAll('#splitTargetBody .split-target-row').forEach(row => {
            if (row !== currentRow) {
                otherTargetTotal += parseFloat(row.querySelector('.target-amount').value || 0);
            }
        });

        const remaining = Math.max(0, totalIn - otherTargetTotal);
        currentRow.querySelector('.target-amount').value = remaining;
        calcTargetTotal();
    }

    function getInTotal() {
        let total = 0;
        document.querySelectorAll('.in-amount').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        return total;
    }

    function calcTargetTotal() {
        let targetTotal = 0;
        document.querySelectorAll('#splitTargetBody .target-amount').forEach(el => {
            targetTotal += parseFloat(el.value) || 0;
        });

        const totalIn = getInTotal();
        document.getElementById('displayTargetTotal').innerText = 'Rp ' + Number(targetTotal).toLocaleString('id-ID');

        const badge = document.getElementById('splitDiffStatusBadgeIn');
        if (!badge) return;

        if (Math.abs(targetTotal - totalIn) < 0.01 && totalIn > 0) {
            badge.className = 'badge badge-light-success fw-bolder fs-8';
            badge.innerText = 'Seimbang (Pas)';
        } else if (targetTotal < totalIn) {
            badge.className = 'badge badge-light-danger fw-bolder fs-8';
            badge.innerText = `Kurang Rp ${Number(totalIn - targetTotal).toLocaleString('id-ID')}`;
        } else {
            badge.className = 'badge badge-light-warning fw-bolder fs-8';
            badge.innerText = `Lebih Rp ${Number(targetTotal - totalIn).toLocaleString('id-ID')}`;
        }
    }

    function addInRow() {
        const tbody = document.getElementById('inBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4">
                <select name="items[${inIndex}][account_id]" class="form-select form-select-solid form-select-sm contra-account-select" required>
                    <option value="">-- Pilih Akun Sumber --</option>
                    @foreach($contraAccounts as $coa)
                        <option value="{{ $coa->id }}">{{ $coa->code }} - {{ $coa->name }} ({{ ucfirst($coa->type) }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${inIndex}][amount]" class="form-control form-control-solid form-control-sm in-amount text-end" value="0" min="1" required oninput="calcInTotal()">
            </td>
            <td>
                <input type="text" name="items[${inIndex}][notes]" class="form-control form-control-solid form-control-sm" placeholder="Memo pos penerimaan...">
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeInRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        inIndex++;
    }

    function removeInRow(btn) {
        const tbody = document.getElementById('inBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
            calcInTotal();
        } else {
            alert('Minimal 1 baris akun.');
        }
    }

    function calcInTotal() {
        const total = getInTotal();
        document.getElementById('displayInTotal').innerText = 'Rp ' + Number(total).toLocaleString('id-ID');
        if (currentPaymentModeIn === 'split') {
            calcTargetTotal();
        }
    }

    function autoFillInvoice(select) {
        const opt = select.options[select.selectedIndex];
        if (opt && opt.value) {
            const amount = opt.getAttribute('data-amount');
            const cust = opt.getAttribute('data-customer');
            const custId = opt.getAttribute('data-customer-id');
            const inv = opt.getAttribute('data-invno');

            // 1. Auto-select Customer Contact
            if (custId) {
                const contactSelect = document.getElementById('cashInContactSelect');
                if (contactSelect) {
                    contactSelect.value = custId;
                    if (window.jQuery && $(contactSelect).data('select2')) {
                        $(contactSelect).trigger('change');
                    }
                }
            }

            // 2. Auto-fill Nominal (Amount)
            const firstAmount = document.querySelector('.in-amount');
            if (firstAmount) {
                firstAmount.value = amount;
            }

            // 3. Auto-select Piutang Usaha (1102) Account in first credit row
            const arAccountId = "{{ $arAccount->id ?? '' }}";
            const firstCoa = document.querySelector('.contra-account-select');
            if (firstCoa && arAccountId) {
                firstCoa.value = arAccountId;
                if (window.jQuery && $(firstCoa).data('select2')) {
                    $(firstCoa).trigger('change');
                }
            }

            // 4. Auto-fill Description & Memo
            const desc = 'Penerimaan Pelunasan Faktur AR ' + inv + ' dari ' + cust;
            document.getElementById('cashInDesc').value = desc;
            const firstNotes = document.querySelector('input[name="items[0][notes]"]');
            if (firstNotes) {
                firstNotes.value = 'Pelunasan Piutang ' + inv;
            }

            calcInTotal();
        }
    }

    // Form submit validation
    document.getElementById('cashInForm').addEventListener('submit', function(e) {
        if (currentPaymentModeIn === 'split') {
            let targetTotal = 0;
            document.querySelectorAll('#splitTargetBody .target-amount').forEach(el => {
                targetTotal += parseFloat(el.value) || 0;
            });
            const inTotal = getInTotal();

            if (Math.abs(targetTotal - inTotal) > 0.01) {
                e.preventDefault();
                alert(`Total rekening penampung dana (Rp ${Number(targetTotal).toLocaleString('id-ID')}) belum sama dengan Total Alokasi Penerimaan (Rp ${Number(inTotal).toLocaleString('id-ID')}). Silakan seimbangkan nominal sebelum posting.`);
                return false;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        setPaymentModeIn('single');
        const invSelect = document.getElementById('salesInvSelect');
        if (invSelect && invSelect.value) {
            autoFillInvoice(invSelect);
        }
    });
</script>
@endsection
