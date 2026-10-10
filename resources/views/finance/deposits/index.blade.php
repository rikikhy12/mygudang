@extends('layouts.app')

@section('title', 'Deposit Customer & Supplier')
@section('page_title', 'Deposit Customer & Supplier')

@section('content')
<div class="d-flex flex-column gap-5">
    
    <!-- Top Nav & Header -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="w-40px h-40px rounded bg-light-success d-flex align-items-center justify-content-center text-success fs-3">
                    <i class="bi bi-piggy-bank-fill"></i>
                </div>
                <div>
                    <h3 class="card-title fw-bolder text-gray-800 m-0 fs-4">
                        Kelola Deposit {{ ucfirst($type === 'customer' ? 'Pelanggan (Customer)' : 'Pemasok (Supplier)') }}
                    </h3>
                    <span class="text-muted fs-7">Pencatatan uang muka titipan, top-up saldo deposit, dan pelunasan transaksi</span>
                </div>
            </div>
            
            <div class="d-flex gap-2 align-items-center">
                <div class="btn-group btn-group-sm">
                    <a href="{{ route('finance.deposits.index', ['type' => 'customer']) }}" class="btn {{ $type === 'customer' ? 'btn-primary fw-bold' : 'btn-light' }}">
                        <i class="bi bi-people-fill me-1"></i> Deposit Customer
                    </a>
                    <a href="{{ route('finance.deposits.index', ['type' => 'supplier']) }}" class="btn {{ $type === 'supplier' ? 'btn-primary fw-bold' : 'btn-light' }}">
                        <i class="bi bi-truck me-1"></i> Deposit Supplier
                    </a>
                </div>

                <button type="button" class="btn btn-sm btn-success fw-bold" onclick="openDepositModal()">
                    <i class="bi bi-plus-circle me-1"></i> Input Mutasi Deposit
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-light-success border border-success border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Deposit Masuk (Top-up)</div>
                        <div class="fs-4 fw-bolder text-success mt-1">Rp {{ number_format($totalDepositsIn, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-success text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-arrow-down-left"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-danger border border-danger border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Total Pemakaian / Refund</div>
                        <div class="fs-4 fw-bolder text-danger mt-1">Rp {{ number_format($totalDepositsOut, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-danger text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light-primary border border-primary border-dashed p-4 rounded-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted fs-8 fw-bold text-uppercase">Sisa Saldo Netto Deposit</div>
                        <div class="fs-4 fw-bolder text-primary mt-1">Rp {{ number_format($currentNetDeposit, 0, ',', '.') }}</div>
                    </div>
                    <div class="w-40px h-40px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fs-4">
                        <i class="bi bi-wallet-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Balances Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title fw-bolder text-gray-800 m-0 fs-5">
                <i class="bi bi-list-ul text-primary me-2"></i> Ringkasan Saldo Deposit Rekanan {{ ucfirst($type === 'customer' ? 'Pelanggan' : 'Supplier') }}
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-4">Kode</th>
                            <th>Nama Rekanan {{ ucfirst($type === 'customer' ? 'Customer' : 'Supplier') }}</th>
                            <th>Kontak</th>
                            <th class="text-end">Total Masuk</th>
                            <th class="text-end">Total Pemakaian</th>
                            <th class="text-end">Sisa Saldo Deposit</th>
                            <th class="text-center pe-4" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 fw-semibold">
                        @forelse($contacts as $contact)
                        @php
                            $in = (float) $contact->deposits->where('transaction_type', 'in')->sum('amount');
                            $out = (float) $contact->deposits->where('transaction_type', 'out')->sum('amount');
                            $balance = $in - $out;
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $contact->code }}</td>
                            <td class="fw-bold text-gray-900">
                                {{ $contact->name }}
                                @if($contact->code === 'CUST-WALKIN')
                                    <span class="badge badge-light-warning fs-9 ms-1">Walk-in</span>
                                @endif
                            </td>
                            <td class="text-muted fs-8">{{ $contact->phone ?? '-' }} &bull; {{ $contact->email ?? '-' }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($in, 0, ',', '.') }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($out, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="badge {{ $balance > 0 ? 'badge-light-success border border-success' : 'badge-light-secondary' }} fs-7 fw-bolder">
                                    Rp {{ number_format($balance, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-xs btn-light-success fw-bold" onclick="openDepositModal({{ $contact->id }})">
                                    <i class="bi bi-plus-circle me-1"></i> Mutasi
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-muted">
                                <i class="bi bi-inbox fs-2x text-muted mb-2 d-block"></i>
                                Tidak ada data rekanan {{ $type }} yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($contacts->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $contacts->appends(['type' => $type, 'search' => request('search')])->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Deposit Ledger History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h4 class="card-title fw-bolder text-gray-800 m-0 fs-5">
                <i class="bi bi-clock-history text-info me-2"></i> Riwayat Mutasi Deposit Terbaru ({{ ucfirst($type) }})
            </h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                    <thead class="table-light">
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                            <th class="ps-4">No. Referensi</th>
                            <th>Tanggal / Waktu</th>
                            <th>Rekanan</th>
                            <th>Tipe Transaksi</th>
                            <th class="text-end">Nominal</th>
                            <th>Keterangan / Jurnal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDeposits as $dep)
                        <tr>
                            <td class="ps-4 fw-bold text-primary">{{ $dep->reference_no ?? 'DEP-'.$dep->id }}</td>
                            <td class="text-muted fs-8">{{ $dep->created_at->format('d/m/Y H:i') }}</td>
                            <td class="fw-bold text-gray-900">{{ $dep->contact?->name }}</td>
                            <td>
                                @if($dep->transaction_type === 'in')
                                    <span class="badge badge-light-success fw-bold">
                                        <i class="bi bi-arrow-down-left me-1"></i> Top-Up / Masuk
                                    </span>
                                @else
                                    <span class="badge badge-light-danger fw-bold">
                                        <i class="bi bi-arrow-up-right me-1"></i> Terpakai / Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="text-end fw-bolder {{ $dep->transaction_type === 'in' ? 'text-success' : 'text-danger' }}">
                                Rp {{ number_format($dep->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <div class="text-gray-800 fs-8">{{ $dep->notes ?? '-' }}</div>
                                @if($dep->journal)
                                    <a href="{{ route('accounting.journals.show', $dep->journal_id) }}" class="text-primary fs-9 text-hover-underline">
                                        Jurnal: {{ $dep->journal->journal_number }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Belum ada riwayat mutasi deposit {{ $type }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Input / Topup Deposit -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('finance.deposits.store') }}" method="POST">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}" />
                <div class="modal-header">
                    <h5 class="modal-title fw-bolder text-gray-900">
                        <i class="bi bi-piggy-bank text-success me-2"></i> Input Transaksi Deposit {{ ucfirst($type) }}
                    </h5>
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x fs-2"></i>
                    </button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label required fs-7 fw-bold mb-0">Pilih {{ ucfirst($type === 'customer' ? 'Pelanggan (Customer)' : 'Pemasok (Supplier)') }}</label>
                            <button type="button" class="btn btn-xs btn-link text-success p-0 fw-bold fs-8" onclick="openQuickContactModal('{{ $type }}', '#modalContactSelect')">
                                <i class="bi bi-plus-circle me-1"></i> Tambah {{ ucfirst($type) }}
                            </button>
                        </div>
                        <select name="contact_id" id="modalContactSelect" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Rekanan --</option>
                            @foreach($allContacts as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label required fs-7 fw-bold">Jenis Mutasi</label>
                            <select name="transaction_type" class="form-select form-select-sm" required>
                                <option value="in">Top-Up Saldo (Masuk)</option>
                                <option value="out">Pengembalian / Refund (Keluar)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label required fs-7 fw-bold">Akun Kas / Bank</label>
                            <select name="account_id" class="form-select form-select-sm" required>
                                @foreach($cashAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required fs-7 fw-bold">Nominal Deposit (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1" min="1" name="amount" class="form-control form-control-sm" placeholder="0" required />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7 fw-bold">Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Contoh: Penerimaan uang muka DP pesanan khusus atau titipan dana..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Mutasi Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function openDepositModal(contactId = null) {
        if (contactId) {
            const sel = document.getElementById('modalContactSelect');
            if (sel) sel.value = contactId;
        }
        const modal = new bootstrap.Modal(document.getElementById('depositModal'));
        modal.show();
    }
</script>
@endsection
@endsection
