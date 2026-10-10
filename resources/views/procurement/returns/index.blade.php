@extends('layouts.app')

@section('title', 'Retur Pembelian (Purchase Returns)')
@section('page_title', 'Daftar Retur Pembelian ke Supplier')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <!-- Header Action Bar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Retur Pembelian (Purchase Return)</h2>
            <p class="text-muted fs-7 mb-0">Kelola pengembalian barang rusak / tidak sesuai ke supplier beserta pemotongan hutang & penyesuaian stok</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('procurement.invoices.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
                <i class="bi bi-receipt me-1"></i> Lihat Faktur AP
            </a>
            <a href="{{ route('procurement.returns.create') }}" class="btn btn-sm btn-danger fw-bold">
                <i class="bi bi-plus-lg me-1"></i> Buat Retur Pembelian
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('procurement.returns.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fs-8 fw-bold text-gray-700">Cari Nomor Retur / Supplier</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-gray-500"></i>
                        <input type="text" name="search" class="form-control form-control-solid form-control-sm ps-9" placeholder="Ketik nomor retur / supplier..." value="{{ request('search') }}" />
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Supplier</label>
                    <select name="supplier_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Semua Supplier --">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-8 fw-bold text-gray-700">Status</label>
                    <select name="status" class="form-select form-select-solid form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai / Posted</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'supplier_id', 'status']))
                        <a href="{{ route('procurement.returns.index') }}" class="btn btn-light btn-sm fw-semibold">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-6 gy-4 mb-0">
                    <thead class="table-light">
                        <tr class="fw-bold text-muted text-uppercase fs-8 bg-light">
                            <th class="ps-6">No. Retur</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Gudang Pengeluaran</th>
                            <th>Faktur AP Terkait</th>
                            <th>Metode Refund</th>
                            <th class="text-end">Total Retur</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-700 fs-7">
                        @forelse($returns as $r)
                        <tr>
                            <td class="ps-6">
                                <a href="{{ route('procurement.returns.show', $r->id) }}" class="fw-bold text-danger text-hover-primary">
                                    {{ $r->return_number }}
                                </a>
                                @if($r->branch)
                                    <span class="badge badge-light-secondary fs-9 d-block mt-0.5" style="max-width: max-content;">{{ $r->branch->name }}</span>
                                @endif
                            </td>
                            <td>{{ $r->return_date->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-bold text-gray-900">{{ $r->supplier->name ?? '-' }}</div>
                                <span class="text-muted fs-9">{{ $r->supplier->code ?? '' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light fs-8">{{ $r->warehouse->name ?? '-' }}</span>
                            </td>
                            <td>
                                @if($r->purchaseInvoice)
                                    <a href="{{ route('procurement.invoices.show', $r->purchase_invoice_id) }}" class="text-primary fs-8 fw-bold">
                                        {{ $r->purchaseInvoice->invoice_number }}
                                    </a>
                                @else
                                    <span class="text-muted fs-8">Retur Langsung</span>
                                @endif
                            </td>
                            <td>
                                @if($r->refund_method === 'potong_hutang')
                                    <span class="badge badge-light-primary fs-8 fw-bold">Potong Hutang AP</span>
                                @else
                                    <span class="badge badge-light-success fs-8 fw-bold">Pengembalian Kas</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger">
                                Rp {{ number_format($r->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($r->status === 'completed')
                                    <span class="badge badge-light-success fw-bold">Selesai</span>
                                @elseif($r->status === 'draft')
                                    <span class="badge badge-light-warning fw-bold">Draft</span>
                                @else
                                    <span class="badge badge-light-danger fw-bold">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-end pe-6">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('procurement.returns.show', $r->id) }}" class="btn btn-icon btn-light btn-sm" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('procurement.returns.print', $r->id) }}" target="_blank" class="btn btn-icon btn-light-primary btn-sm" title="Cetak Nota Retur">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-10 text-muted">
                                <i class="bi bi-box-arrow-left fs-2tx d-block mb-2 text-gray-400"></i>
                                <span class="fw-bold">Belum ada transaksi retur pembelian.</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
                <div class="card-footer py-3 px-6">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
