@extends('layouts.app')

@section('title', 'Opname ' . $adjustment->adjustment_number)
@section('page_title', 'Detail Penyesuaian Stok: ' . $adjustment->adjustment_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Opname
        </a>
    </div>
    <button class="btn btn-sm btn-light-info" onclick="window.print()">
        <i class="bi bi-printer me-1"></i> Cetak Hasil Opname
    </button>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title fs-4 fw-bold text-gray-900 m-0">{{ $adjustment->adjustment_number }}</h3>
            <div class="fs-7 text-muted mt-1">
                Petugas: <strong class="text-gray-800">{{ $adjustment->adjustedBy->name }}</strong> | Tanggal: <strong>{{ $adjustment->adjustment_date->format('d F Y') }}</strong>
            </div>
        </div>
        <div>
            <span class="badge badge-light-success fw-bold px-3 py-2">Posted</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Lokasi Gudang</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $adjustment->warehouse->name }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light p-3 rounded-3 h-100 border border-gray-200">
                    <div class="fs-8 fw-bold text-muted text-uppercase">Alasan Opname</div>
                    <div class="fs-6 fw-bold text-gray-900 mt-1">{{ $adjustment->reason }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-light-primary p-3 rounded-3 h-100 border border-primary border-dashed">
                    <div class="fs-8 fw-bold text-primary text-uppercase">Jurnal Selisih Persediaan</div>
                    @if($adjustment->journal)
                        <div class="fs-6 fw-bold text-success mt-1">
                            <a href="{{ route('accounting.journals.show', $adjustment->journal_id) }}" class="text-success">{{ $adjustment->journal->journal_number }}</a>
                        </div>
                    @else
                        <div class="fs-7 text-muted mt-1">Tidak ada jurnal (Tidak ada deviasi nilai)</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th class="ps-3 py-3" style="width: 50px;">No.</th>
                        <th>Kode & Nama Barang</th>
                        <th class="text-center">Stok Sistem</th>
                        <th class="text-center">Stok Fisik Nyata</th>
                        <th class="text-center">Selisih (Variance)</th>
                        <th class="text-end">Harga Pokok</th>
                        <th class="text-end pe-3">Nilai Deviasi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 fw-semibold">
                    @foreach($adjustment->items as $idx => $item)
                    <tr>
                        <td class="ps-3">{{ $idx + 1 }}</td>
                        <td><strong class="text-gray-900">{{ $item->item->code }}</strong> - {{ $item->item->name }}</td>
                        <td class="text-center">{{ number_format($item->system_stock, 0) }} {{ $item->item->unit->symbol }}</td>
                        <td class="text-center fw-bold">{{ number_format($item->physical_stock, 0) }} {{ $item->item->unit->symbol }}</td>
                        <td class="text-center fw-bold {{ $item->difference_qty < 0 ? 'text-danger' : ($item->difference_qty > 0 ? 'text-success' : 'text-muted') }}">
                            {{ $item->difference_qty > 0 ? '+' : '' }}{{ number_format($item->difference_qty, 0) }} {{ $item->item->unit->symbol }}
                        </td>
                        <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                        <td class="text-end pe-3 fw-bold text-gray-900">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
