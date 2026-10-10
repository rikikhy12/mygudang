@extends('layouts.app')

@section('title', 'Tagihan Pembelian (AP)')
@section('page_title', 'Faktur Tagihan Pemasok (Accounts Payable)')

@section('content')
<div class="card card-flush shadow-sm">
    <div class="card-header bg-white py-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <div class="w-40px h-40px rounded-3 bg-light-primary text-primary d-flex align-items-center justify-content-center me-3 shadow-xs">
                <i class="bi bi-receipt fs-3 text-primary"></i>
            </div>
            <div>
                <h3 class="card-title fw-bolder text-gray-900 m-0 fs-5">Faktur Tagihan Pemasok & Hutang (AP)</h3>
                <span class="text-muted fs-8 fw-semibold">Pencatatan invoice vendor, pembayaran kas/bank, dan pemantauan jatuh tempo hutang</span>
            </div>
        </div>
        <div class="card-toolbar">
            <a href="{{ route('procurement.invoices.create') }}" class="btn btn-primary btn-sm fw-bold">
                <i class="bi bi-plus-circle me-1"></i> Catat Tagihan Baru
            </a>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 dataTable no-footer table">
                <thead>
                    <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                        <th>No. Tagihan ERP</th>
                        <th>No. Faktur Supplier</th>
                        <th>Supplier</th>
                        <th>Tgl Tagihan</th>
                        <th>Jatuh Tempo</th>
                        <th>Grand Total</th>
                        <th>Sisa Hutang</th>
                        <th>Status</th>
                        <th class="text-end min-w-140px no-sort">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('procurement.invoices.show', $inv->id) }}" class="text-gray-800 fw-bold text-hover-primary">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $inv->supplier_invoice_number ?: '-' }}</td>
                        <td><span class="text-gray-800 fw-semibold">{{ $inv->supplier->name }}</span></td>
                        <td>{{ $inv->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="{{ $inv->due_date < now() && $inv->status != 'paid' ? 'text-danger fw-bold' : '' }}">
                                {{ $inv->due_date->format('d/m/Y') }}
                            </span>
                        </td>
                        <td><span class="text-gray-900 fw-bolder">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</span></td>
                        <td>
                            <span class="fw-bolder {{ $inv->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                Rp {{ number_format($inv->remaining_balance, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            @if($inv->status == 'paid')
                                <span class="badge badge-light-success fw-bold">Paid (Lunas)</span>
                            @elseif($inv->status == 'partial')
                                <span class="badge badge-light-warning fw-bold">Partial</span>
                            @else
                                <span class="badge badge-light-danger fw-bold">Unpaid</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary dropdown-toggle py-1 px-3 fs-8" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    Aksi
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm fs-7">
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('procurement.invoices.show', $inv->id) }}">
                                            <i class="bi bi-eye text-info me-2"></i> Lihat Detail Faktur
                                        </a>
                                    </li>
                                    @if($inv->status !== 'paid')
                                    <li>
                                        <a class="dropdown-item py-2 text-primary" href="{{ route('procurement.invoices.edit', $inv->id) }}">
                                            <i class="bi bi-pencil-square text-primary me-2"></i> Edit Faktur Tagihan
                                        </a>
                                    </li>
                                    @endif
                                    @if($inv->remaining_balance > 0)
                                    <li>
                                        <a class="dropdown-item py-2 text-danger" href="{{ route('finance.cash-out.create', ['purchase_invoice_id' => $inv->id]) }}">
                                            <i class="bi bi-cash-stack text-danger me-2"></i> Bayar Kas Keluar
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
