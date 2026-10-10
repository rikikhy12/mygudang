@extends('layouts.app')

@section('title', 'Input Transaksi Deposit ' . ucfirst($type === 'customer' ? 'Pelanggan' : 'Supplier'))
@section('page_title', 'Form Mutasi Deposit ' . ucfirst($type === 'customer' ? 'Pelanggan (Customer)' : 'Pemasok (Supplier)'))

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">
                Input Transaksi Mutasi Deposit {{ ucfirst($type === 'customer' ? 'Pelanggan (Customer)' : 'Pemasok (Supplier)') }}
            </h2>
            <p class="text-muted fs-7 mb-0">Catat penerimaan uang muka, penambahan saldo deposit, atau pengembalian dana (refund) rekanan</p>
        </div>
        <a href="{{ $type === 'supplier' ? route('finance.deposits.supplier') : route('finance.deposits.customer') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Deposit {{ ucfirst($type) }}
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-3 rounded-3 fs-7">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-piggy-bank-fill {{ $type === 'customer' ? 'text-primary' : 'text-success' }} fs-3"></i>
                    Formulir Mutasi Deposit {{ ucfirst($type === 'customer' ? 'Pelanggan' : 'Supplier') }}
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan isi identitas rekanan, jenis transaksi deposit, rekening penampung, dan nominal
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.deposits.store') }}">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}" />

            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Identitas Rekanan & Jenis Mutasi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fs-7 fw-semibold text-gray-700 required mb-0">
                                Pilih {{ ucfirst($type === 'customer' ? 'Pelanggan (Customer)' : 'Pemasok (Supplier)') }}
                            </label>
                            <button type="button" class="btn btn-xs btn-link p-0 fw-bold fs-8 text-primary" onclick="openQuickContactModal('{{ $type }}', '#depositContactSelect')">
                                <i class="bi bi-plus-circle me-1"></i> Buat {{ ucfirst($type) }} Baru
                            </button>
                        </div>
                        <select name="contact_id" id="depositContactSelect" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="">-- Pilih Rekanan {{ ucfirst($type) }} --</option>
                            @foreach($contacts as $c)
                                <option value="{{ $c->id }}" {{ (old('contact_id', $contactId) == $c->id) ? 'selected' : '' }}>
                                    {{ $c->code }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Jenis Mutasi Deposit</label>
                        <select name="transaction_type" class="form-select form-select-solid form-select-sm" data-control="select2" data-hide-search="true" required>
                            <option value="in" {{ old('transaction_type') == 'in' ? 'selected' : '' }}>
                                {{ $type === 'customer' ? 'Penerimaan / Top-Up Deposit (Dana Masuk ke Kas/Bank)' : 'Penyetoran Uang Muka / Deposit ke Supplier (Dana Keluar)' }}
                            </option>
                            <option value="out" {{ old('transaction_type') == 'out' ? 'selected' : '' }}>
                                {{ $type === 'customer' ? 'Pengembalian / Refund Deposit Pelanggan (Dana Keluar)' : 'Pengembalian / Refund Uang Muka dari Supplier (Dana Masuk)' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Rekening Kas/Bank & Nominal Mutasi</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Rekening Kas / Bank Penampung</label>
                        <select name="account_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($cashAccounts as $acc)
                                <option value="{{ $acc->id }}" {{ old('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nominal Mutasi (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="any" min="1" name="amount" class="form-control form-control-solid form-control-sm text-end fw-bold" value="{{ old('amount') }}" placeholder="0" required />
                        </div>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">3</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Catatan & Keterangan</h4>
                </div>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Keterangan Transaksi</label>
                        <textarea name="notes" class="form-control form-control-solid form-control-sm" rows="3" placeholder="Contoh: Penerimaan uang muka titipan pesanan khusus, DP proyek, atau refund ke pelanggan...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ $type === 'supplier' ? route('finance.deposits.supplier') : route('finance.deposits.customer') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Mutasi & Posting Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
