@extends('layouts.app')

@section('title', 'Detail Retur Penjualan ' . $return->return_number)
@section('page_title', 'Detail Retur Penjualan: ' . $return->return_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('sales.returns.index') }}" class="btn btn-sm btn-light fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Retur
        </a>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('sales.returns.print', $return->id) }}" target="_blank" class="btn btn-sm btn-primary">
            <i class="bi bi-printer me-1"></i> Cetak Nota Retur
        </a>
        <a href="{{ route('sales.returns.create') }}" class="btn btn-sm btn-warning text-dark fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Buat Retur Baru
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0 d-flex align-items-center gap-2">
                <i class="bi bi-arrow-return-left text-warning fs-3"></i> {{ $return->return_number }}
            </h3>
            <div class="fs-7 text-muted mt-1">
                Tanggal Retur: <strong>{{ $return->return_date->format('d F Y') }}</strong> | Dibuat oleh: <strong>{{ $return->createdBy->name ?? 'System' }}</strong>
            </div>
        </div>
        <div>
            @if($return->status === 'completed')
                <span class="badge badge-light-success fw-bold px-3 py-2 fs-7">Selesai / Terposting</span>
            @elseif($return->status === 'draft')
                <span class="badge badge-light-warning fw-bold px-3 py-2 fs-7">Draft</span>
            @else
                <span class="badge badge-light-danger fw-bold px-3 py-2 fs-7">Dibatalkan</span>
            @endif
        </div>
    </div>

    <div class="card-body p-6">
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="bg-light p-4 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase mb-1">Pelanggan / Customer</div>
                    <div class="fs-6 fw-bold text-gray-900">{{ $return->customer->name ?? '-' }}</div>
                    <div class="fs-7 text-muted mt-1">Kode: <strong>{{ $return->customer->code ?? '-' }}</strong></div>
                    <div class="fs-7 text-muted mt-0.5">Telepon: {{ $return->customer->phone ?? '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-4 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase mb-1">Lokasi & Referensi</div>
                    <div class="fs-7 text-gray-800 mt-1">
                        Gudang Penerima: <span class="badge badge-light-success fw-bold">{{ $return->warehouse->name ?? '-' }}</span>
                    </div>
                    @if($return->branch)
                        <div class="fs-7 text-gray-800 mt-1">
                            Cabang: <strong>{{ $return->branch->name }}</strong>
                        </div>
                    @endif
                    <div class="fs-7 text-gray-800 mt-1">
                        Faktur AR Terkait: 
                        @if($return->salesInvoice)
                            <a href="{{ route('sales.invoices.show', $return->sales_invoice_id) }}" class="text-primary fw-bold">
                                {{ $return->salesInvoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-muted">Retur Langsung</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-warning p-4 rounded-3 h-100 border border-warning border-dashed">
                    <div class="fs-8 fw-bold text-warning text-uppercase mb-1">Metode Kompensasi & Jurnal</div>
                    <div class="fs-7 fw-bold text-gray-900 mt-1">
                        @if($return->refund_method === 'potong_piutang')
                            <span class="badge badge-light-warning text-dark fw-bold">Potong Piutang Usaha (AR)</span>
                        @else
                            <span class="badge badge-light-danger fw-bold">Pengembalian Kas Refund</span>
                            <div class="fs-8 text-muted mt-1">Rekening: {{ $return->cashAccount->name ?? '-' }}</div>
                        @endif
                    </div>
                    @if($return->journal)
                        <div class="fs-7 text-success fw-bold mt-2">
                            Jurnal: <a href="{{ route('accounting.journals.show', $return->journal_id) }}" class="text-success text-decoration-underline">{{ $return->journal->journal_number }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($return->notes)
        <div class="alert alert-light-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan:</strong> {{ $return->notes }}
        </div>
        @endif

        <h5 class="fs-6 fw-bold text-gray-800 mb-3">Daftar Barang yang Dikembalikan Pelanggan</h5>
        <div class="table-responsive rounded border mb-4">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-4 py-3" style="width: 50px;">No.</th>
                        <th>Kode & Nama Barang</th>
                        <th class="text-center" style="width: 140px;">Qty Retur</th>
                        <th class="text-end" style="width: 180px;">Harga Satuan</th>
                        <th class="text-end" style="width: 180px;">Subtotal</th>
                        <th class="pe-4" style="min-width: 180px;">Alasan Retur</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($return->items as $idx => $item)
                    <tr>
                        <td class="ps-4">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-gray-900">{{ $item->item->code }}</strong> - {{ $item->item->name }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-success fw-bold fs-7">
                                {{ number_format($item->quantity, 2) }} {{ $item->unit->symbol ?? $item->item->unit->symbol ?? 'Pcs' }}
                            </span>
                        </td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-gray-900">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                        <td class="pe-4 text-muted">{{ $item->reason ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <div class="card bg-light border border-gray-300 p-4" style="min-width: 360px;">
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>Subtotal Retur:</span>
                    <span class="text-gray-900 fw-bold">Rp {{ number_format($return->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 fs-7 text-muted">
                    <span>Pajak PPN:</span>
                    <span class="text-gray-900 fw-bold">Rp {{ number_format($return->tax_amount, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top border-gray-400">
                    <span class="text-gray-900 fw-bolder fs-6">Total Nilai Retur:</span>
                    <span class="text-warning fw-bolder fs-4 text-dark">Rp {{ number_format($return->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Journal Entries Accordion / Box -->
        @if($return->journal && $return->journal->entries)
        <div class="mt-5 pt-4 border-top">
            <h5 class="fs-6 fw-bold text-gray-800 mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-journal-text text-primary"></i> Rincian Entri Jurnal Akuntansi (#{{ $return->journal->journal_number }})
            </h5>
            <div class="table-responsive rounded border">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-2 fs-8 mb-0">
                    <thead>
                        <tr class="fw-bold text-muted text-uppercase bg-light">
                            <th class="ps-4">Akun Rekening</th>
                            <th>Memo</th>
                            <th class="text-end" style="width: 160px;">Debit</th>
                            <th class="text-end pe-4" style="width: 160px;">Kredit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->journal->entries as $entry)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-gray-900">{{ $entry->account->code }}</span> - {{ $entry->account->name }}
                            </td>
                            <td class="text-muted">{{ $entry->memo }}</td>
                            <td class="text-end fw-semibold text-gray-900">
                                {{ $entry->type === 'debit' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="text-end pe-4 fw-semibold text-gray-900">
                                {{ $entry->type === 'credit' ? 'Rp ' . number_format($entry->amount, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
