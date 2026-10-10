@extends('layouts.app')

@section('title', 'Terbitkan Surat Jalan')
@section('page_title', 'Formulir Surat Jalan / Delivery Order (DO)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Terbitkan Surat Jalan (Delivery Order)</h2>
            <p class="text-muted fs-7 mb-0">Kirim barang fisik dari gudang berdasarkan pesanan penjualan (SO)</p>
        </div>
        <a href="{{ route('sales.do.index') }}" class="btn btn-light-primary btn-sm fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Surat Jalan
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-primary fs-3"></i> Formulir Surat Jalan Pengiriman Barang
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan pilih pesanan SO, tentukan gudang asal, tanggal pengiriman, dan rincian kuantitas kirim
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('sales.do.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Pengiriman & Rujukan Pesanan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Pilih Sales Order (SO)</label>
                        <select name="sales_order_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Sales Order --" required onchange="if(this.value) window.location.href='{{ route('sales.do.create') }}?so_id=' + this.value;">
                            <option value="">-- Pilih Sales Order --</option>
                            @foreach($openOrders as $oSo)
                                <option value="{{ $oSo->id }}" {{ $selectedSo && $selectedSo->id == $oSo->id ? 'selected' : '' }}>
                                    {{ $oSo->so_number }} - {{ $oSo->customer->name }} (Rp {{ number_format($oSo->grand_total, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Gudang Asal Pengeluaran</label>
                        <select name="warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Pengiriman</label>
                        <input type="date" name="delivery_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nama Pengemudi / Kurir Ekspedisi</label>
                        <input type="text" name="driver_name" class="form-control form-control-solid form-control-sm" placeholder="Contoh: Pak Joko / Driver Logistik">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Nomor Polisi Armada / No. Resi</label>
                        <input type="text" name="vehicle_number" class="form-control form-control-solid form-control-sm" placeholder="Contoh: B 1234 CD">
                    </div>

                    <div class="col-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan Pengiriman</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Tanda terima gudang penerima, kondisi segel...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">
                        Rincian Barang yang Dikirim 
                        @if($selectedSo)
                            <span class="text-muted fs-7 fw-normal ms-1">(Berdasarkan SO: <strong>{{ $selectedSo->so_number }}</strong>)</span>
                        @endif
                    </h4>
                </div>

                @if($selectedSo)
                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3">Kode & Nama Barang</th>
                                <th class="text-center">Total Dipesan</th>
                                <th class="text-center" style="width: 110px;">Koli (Ket)</th>
                                <th class="text-center">Sudah Dikirim</th>
                                <th class="text-center">Sisa Belum Dikirim</th>
                                <th class="text-end pe-4" style="width: 200px;">Jumlah Dikirim Saat Ini</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 fw-semibold">
                            @foreach($selectedSo->items as $idx => $sItem)
                            @if($sItem->pending_quantity > 0)
                            <tr>
                                <td class="ps-4">
                                    <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $sItem->item_id }}">
                                    <span class="text-gray-900 fw-bold">{{ $sItem->item->code }}</span> - {{ $sItem->item->name }}
                                </td>
                                <td class="text-center">{{ number_format($sItem->quantity, 0) }} {{ $sItem->item->unit->symbol }}</td>
                                <td>
                                    <input type="text" name="items[{{ $idx }}][koli]" class="form-control form-control-solid form-control-sm text-center" value="{{ $sItem->koli }}" placeholder="Koli">
                                </td>
                                <td class="text-center">{{ number_format($sItem->delivered_quantity, 0) }}</td>
                                <td class="text-center"><span class="badge badge-light-warning fw-bold">{{ number_format($sItem->pending_quantity, 0) }}</span></td>
                                <td class="pe-4">
                                    <input type="number" step="0.01" name="items[{{ $idx }}][quantity]" class="form-control form-control-solid form-control-sm text-end" value="{{ $sItem->pending_quantity }}" max="{{ $sItem->pending_quantity }}" min="0.01" required>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning d-flex align-items-center p-4 mb-2 rounded-3 fs-7">
                    <i class="bi bi-info-circle-fill fs-2 text-warning me-3"></i>
                    <div class="d-flex flex-column text-gray-800">
                        <strong>Catatan Otomasi Akuntansi:</strong>
                        <span>Memposting surat jalan ini akan mengurangi saldo stok fisik di gudang terkait dan otomatis menjurnal Beban Pokok Penjualan (HPP) vs Persediaan.</span>
                    </div>
                </div>
                @else
                <div class="border rounded-3 text-center py-5 text-muted bg-light mb-2">
                    <i class="bi bi-bag-check fs-2x text-muted mb-2 d-block"></i>
                    <div class="fw-semibold fs-7">Silakan pilih Sales Order (SO) di atas untuk memuat daftar barang yang siap dikirim.</div>
                </div>
                @endif
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('sales.do.index') }}" class="btn btn-sm btn-light">Batal</a>
                @if($selectedSo)
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-truck me-1"></i> Konfirmasi & Posting Surat Jalan
                </button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection
