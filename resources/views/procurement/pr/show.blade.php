@extends('layouts.app')

@section('title', 'PR ' . $pr->pr_number)
@section('page_title', 'Detail Permintaan Pembelian: ' . $pr->pr_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('procurement.pr.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar PR
        </a>
    </div>

    <div class="d-flex gap-2">
        @if($pr->status == 'pending_approval' && (auth()->user()->hasRole('admin') || auth()->user()->hasPermission('approve_procurement')))
            <form action="{{ route('procurement.pr.approve', $pr->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-success">
                    <i class="bi bi-check-circle me-1"></i> Setujui (Approve)
                </button>
            </form>
            <form action="{{ route('procurement.pr.reject', $pr->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-light-danger">
                    <i class="bi bi-x-circle me-1"></i> Tolak (Reject)
                </button>
            </form>
        @endif

        @if($pr->status == 'approved')
            <a href="{{ route('procurement.po.create', ['pr_id' => $pr->id]) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-cart-plus me-1"></i> Buat Purchase Order (PO)
            </a>
        @endif
        <button class="btn btn-sm btn-light-info" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak PR
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $pr->pr_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Dibuat oleh: <strong class="text-gray-800">{{ $pr->requester->name }}</strong> pada {{ $pr->request_date->format('d F Y') }}
            </div>
        </div>
        <div>
            @if($pr->status == 'approved')
                <span class="badge badge-light-success fw-bold px-3 py-2">Approved</span>
            @elseif($pr->status == 'pending_approval')
                <span class="badge badge-light-warning fw-bold px-3 py-2">Pending Approval</span>
            @elseif($pr->status == 'rejected')
                <span class="badge badge-light-danger fw-bold px-3 py-2">Rejected</span>
            @elseif($pr->status == 'completed')
                <span class="badge badge-light-info fw-bold px-3 py-2">Completed in PO</span>
            @else
                <span class="badge badge-light-dark fw-bold px-3 py-2">Draft</span>
            @endif
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Departemen Pemohon</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $pr->department ?: '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Target Tanggal Dibutuhkan</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $pr->required_date ? $pr->required_date->format('d/m/Y') : '-' }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Disetujui Oleh</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $pr->approver ? $pr->approver->name : 'Menunggu Approval' }}</div>
                </div>
            </div>
        </div>

        @if($pr->notes)
        <div class="alert alert-secondary p-3 mb-4 rounded-3 fs-7">
            <strong>Catatan Pemohon:</strong> {{ $pr->notes }}
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode & Nama Barang</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah (Qty)</th>
                        <th class="text-end">Estimasi Harga Satuan</th>
                        <th class="text-end pe-3">Total Estimasi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @php $totalEst = 0; @endphp
                    @foreach($pr->items as $idx => $item)
                    @php $totalEst += $item->estimated_total_price; @endphp
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-gray-900">{{ $item->item->code }}</strong> - {{ $item->item->name }}
                        </td>
                        <td class="text-center">{{ $item->item->unit->symbol }}</td>
                        <td class="text-center fw-bold text-primary">{{ number_format($item->quantity, 0) }}</td>
                        <td class="text-end">Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->estimated_total_price, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr class="bg-light fw-bold">
                        <td colspan="5" class="text-end fs-7 text-muted ps-3">Total Estimasi Kebutuhan Pengadaan:</td>
                        <td class="text-end pe-3 text-primary fs-6">Rp {{ number_format($totalEst, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
