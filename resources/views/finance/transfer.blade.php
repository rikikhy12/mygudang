@extends('layouts.app')

@section('title', 'Transfer Antar Rekening')
@section('page_title', 'Transfer Saldo Kas & Bank Antar Rekening')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Transfer Saldo Antar Kas & Bank</h2>
            <p class="text-muted fs-7 mb-0">Pemindahan dana likuid antar rekening kas, bank, atau rekening penampung</p>
        </div>
        <a href="{{ route('finance.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Mutasi Kas & Bank
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left-right text-info fs-3"></i> Formulir Pemindahan Saldo Antar Rekening
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan tentukan rekening asal pengirim, rekening tujuan penerima, dan nominal yang dipindahkan
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('finance.transfer.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Rekening Asal & Rekening Tujuan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Rekening Asal (Pengirim / Kredit)</label>
                        <select name="account_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="">-- Pilih Rekening Asal --</option>
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}" {{ old('account_id') == $ca->id ? 'selected' : '' }}>
                                    {{ $ca->code }} - {{ $ca->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Rekening Tujuan (Penerima / Debit)</label>
                        <select name="target_account_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            <option value="">-- Pilih Rekening Tujuan --</option>
                            @foreach($cashAccounts as $ca)
                                <option value="{{ $ca->id }}" {{ old('target_account_id') == $ca->id ? 'selected' : '' }}>
                                    {{ $ca->code }} - {{ $ca->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Jadwal, Nominal & Keterangan Transfer</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Transfer</label>
                        <input type="date" name="transaction_date" class="form-control form-control-solid form-control-sm" value="{{ old('transaction_date', date('Y-m-d')) }}" required />
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Nominal Transfer (Rp)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted">Rp</span>
                            <input type="number" step="0.01" name="amount" class="form-control form-control-solid form-control-sm" value="{{ old('amount') }}" required placeholder="Jumlah transfer..." min="1" />
                        </div>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Keterangan / Alasan Transfer</label>
                        <input type="text" name="description" class="form-control form-control-solid form-control-sm" value="{{ old('description') }}" required placeholder="Contoh: Pengisian kas kecil kasir, pemindahan saldo operasional..." />
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center p-4 mb-0 rounded-3">
                    <i class="bi bi-info-circle-fill fs-2 text-info me-3"></i>
                    <div class="fs-7 text-gray-800">
                        Jurnal Transfer Antar Kas/Bank akan otomatis terbentuk (Debit Akun Penerima vs Kredit Akun Pengirim) tanpa mempengaruhi Laba Rugi perusahaan.
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('finance.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-info">
                    <i class="bi bi-check2-circle me-1"></i> Eksekusi Transfer Kas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
