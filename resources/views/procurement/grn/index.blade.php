@extends('layouts.app')

@section('title', 'Penerimaan Barang (GRN)')
@section('page_title', 'Penerimaan Barang Fisik (Goods Receipt Note)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-box-arrow-in-down fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Penerimaan Barang Fisik (Goods Receipt Note)</h3>
                <span class="text-muted fs-8 fw-semibold">Verifikasi fisik barang dari supplier, pencocokan PO, dan penambahan saldo stok gudang</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('procurement.grn.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Catat Penerimaan Barang Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. GRN</th>
                        <th>No. PO Induk</th>
                        <th>Supplier</th>
                        <th>Gudang Masuk</th>
                        <th>Tanggal Penerimaan</th>
                        <th>No. Surat Jalan Pemasok</th>
                        <th>Jurnal Akuntansi</th>
                        <th class="text-end min-w-140px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($receipts as $grn)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.grn.show', $grn->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $grn->grn_number }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('procurement.po.show', $grn->purchase_order_id) }}" class="text-primary fw-semibold">
                                {{ $grn->purchaseOrder->po_number }}
                            </a>
                        </td>
                        <td><span class="text-gray-800 fw-semibold">{{ $grn->supplier->name }}</span></td>
                        <td>{{ $grn->warehouse->name }}</td>
                        <td>{{ $grn->receipt_date->format('d/m/Y') }}</td>
                        <td>{{ $grn->delivery_note_number ?: '-' }}</td>
                        <td>
                            @if($grn->journal)
                                <a href="{{ route('accounting.journals.show', $grn->journal_id) }}" class="badge badge-light-success fw-bold">
                                    <i class="bi bi-journal-check me-1"></i> {{ $grn->journal->journal_number }}
                                </a>
                            @else
                                <span class="badge badge-light-secondary">No Journal</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('procurement.grn.show', $grn->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail GRN
                                        </a>
                                    </li>
                                    @if($grn->invoices->count() == 0)
                                    <li>
                                        <a class="dropdown-item py-2 text-primary" href="{{ route('procurement.invoices.create', ['grn_id' => $grn->id]) }}">
                                            <i class="bi bi-receipt text-primary me-2"></i> Buat Tagihan (AP)
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
