@extends('layouts.app')

@section('title', 'Input Jurnal Manual')
@section('page_title', 'Formulir Jurnal Umum Manual')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Input Jurnal Penyesuaian & Umum</h2>
            <p class="text-muted fs-7 mb-0">Catat transaksi ayat jurnal berpasangan (Double-Entry Debit & Kredit)</p>
        </div>
        <a href="{{ route('accounting.journals.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Jurnal Umum
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-plus text-primary fs-3"></i> Formulir Transaksi Jurnal Umum
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi informasi transaksi, tanggal, nomor referensi, serta rincian debet & kredit
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('accounting.journals.store') }}" id="journalForm">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Header Jurnal</h4>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Transaksi</label>
                        <input type="date" name="transaction_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">No. Referensi / Bukti Transaksi</label>
                        <input type="text" name="reference_no" class="form-control form-control-solid form-control-sm" placeholder="contoh: MEMO-001, BUKTI-99">
                    </div>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Deskripsi Jurnal</label>
                        <input type="text" name="description" class="form-control form-control-solid form-control-sm" required placeholder="contoh: Jurnal Penyesuaian Akrual Gaji Akhir Periode...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Ayat Jurnal (Double-Entry Debit & Kredit)</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-warning fw-bold" onclick="openQuickCoaModal('.journal-account-select', handleJournalNewCoa)">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Akun COA Baru
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addJournalRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table" id="journalTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-3 py-3" style="width: 38%;">Akun COA</th>
                                <th style="width: 15%;">Posisi</th>
                                <th style="width: 22%;">Nominal (Rp)</th>
                                <th style="width: 20%;">Memo / Catatan</th>
                                <th class="text-end pe-3" style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="journalBody" class="fw-semibold text-gray-700">
                            <tr>
                                <td class="ps-3">
                                    <select name="entries[0][account_id]" class="form-select form-select-solid form-select-sm journal-account-select" required>
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="entries[0][type]" class="form-select form-select-solid form-select-sm pos-select" required onchange="calcJournalBalance()">
                                        <option value="debit">DEBIT</option>
                                        <option value="credit">CREDIT</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="entries[0][amount]" class="form-control form-control-solid form-control-sm amount-input text-end" value="0" min="0.01" required oninput="calcJournalBalance()">
                                </td>
                                <td>
                                    <input type="text" name="entries[0][memo]" class="form-control form-control-solid form-control-sm" placeholder="Memo baris...">
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-light-danger btn-icon" onclick="removeJournalRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td class="ps-3">
                                    <select name="entries[1][account_id]" class="form-select form-select-solid form-select-sm journal-account-select" required>
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="entries[1][type]" class="form-select form-select-solid form-select-sm pos-select" required onchange="calcJournalBalance()">
                                        <option value="credit">CREDIT</option>
                                        <option value="debit">DEBIT</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="entries[1][amount]" class="form-control form-control-solid form-control-sm amount-input text-end" value="0" min="0.01" required oninput="calcJournalBalance()">
                                </td>
                                <td>
                                    <input type="text" name="entries[1][memo]" class="form-control form-control-solid form-control-sm" placeholder="Memo baris...">
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-light-danger btn-icon" onclick="removeJournalRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Balance Indicator Card -->
                <div class="d-flex justify-content-end mb-2">
                    <div class="card bg-light border border-gray-200 p-4 rounded-3" style="min-width: 360px;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-gray-600 fs-7">Total Debit:</span>
                            <span class="text-success fw-bold fs-6" id="displayTotalDebit">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-gray-600 fs-7">Total Credit:</span>
                            <span class="text-primary fw-bold fs-6" id="displayTotalCredit">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-300">
                            <span class="text-gray-800 fw-bold fs-7">Status:</span>
                            <span id="balanceStatusBadge" class="badge badge-light-warning">Belum Balance</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('accounting.journals.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary" id="saveJrnBtn" disabled>
                    <i class="bi bi-check2-circle me-1"></i> Simpan & Posting Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let jrnIndex = 2;
    function addJournalRow() {
        const tbody = document.getElementById('journalBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-3">
                <select name="entries[${jrnIndex}][account_id]" class="form-select form-select-solid form-select-sm journal-account-select" required>
                    <option value="">-- Pilih Akun --</option>
                    @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select name="entries[${jrnIndex}][type]" class="form-select form-select-solid form-select-sm pos-select" required onchange="calcJournalBalance()">
                    <option value="debit">DEBIT</option>
                    <option value="credit">CREDIT</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="entries[${jrnIndex}][amount]" class="form-control form-control-solid form-control-sm amount-input text-end" value="0" min="0.01" required oninput="calcJournalBalance()">
            </td>
            <td>
                <input type="text" name="entries[${jrnIndex}][memo]" class="form-control form-control-solid form-control-sm" placeholder="Memo baris...">
            </td>
            <td class="text-end pe-3">
                <button type="button" class="btn btn-sm btn-light-danger btn-icon" onclick="removeJournalRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        jrnIndex++;
        calcJournalBalance();
    }

    function handleJournalNewCoa(account) {
        document.querySelectorAll('.journal-account-select').forEach(select => {
            const opt = new Option(account.label || `${account.code} - ${account.name}`, account.id);
            select.add(opt);
        });
    }

    function removeJournalRow(btn) {
        const tbody = document.getElementById('journalBody');
        if (tbody.children.length > 2) {
            btn.closest('tr').remove();
            calcJournalBalance();
        } else {
            alert('Minimal harus ada 2 baris ayat jurnal (Debit & Kredit).');
        }
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function calcJournalBalance() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('#journalBody tr').forEach(tr => {
            const pos = tr.querySelector('.pos-select').value;
            const amount = parseFloat(tr.querySelector('.amount-input').value) || 0;
            if (pos === 'debit') {
                totalDebit += amount;
            } else {
                totalCredit += amount;
            }
        });

        document.getElementById('displayTotalDebit').innerText = formatRupiah(totalDebit);
        document.getElementById('displayTotalCredit').innerText = formatRupiah(totalCredit);

        const badge = document.getElementById('balanceStatusBadge');
        const saveBtn = document.getElementById('saveJrnBtn');

        if (totalDebit > 0 && Math.abs(totalDebit - totalCredit) < 0.01) {
            badge.className = 'badge badge-light-success';
            badge.innerText = 'Balanced (Seimbang)';
            saveBtn.disabled = false;
        } else {
            badge.className = 'badge badge-light-danger';
            badge.innerText = 'Selisih: ' + formatRupiah(Math.abs(totalDebit - totalCredit));
            saveBtn.disabled = true;
        }
    }
</script>
@endsection

