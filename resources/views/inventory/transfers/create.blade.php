@extends('layouts.app')

@section('title', 'Buat Transfer Antar Gudang')
@section('page_title', 'Formulir Pemindahan Barang Antar Gudang')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Transfer Antar Gudang</h2>
            <p class="text-muted fs-7 mb-0">Pindahkan stok persediaan barang dari satu cabang/gudang ke gudang lainnya</p>
        </div>
        <a href="{{ route('inventory.transfers.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transfer
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left-right text-primary fs-3"></i> Formulir Transfer Antar Gudang
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan tentukan gudang asal, gudang tujuan, tanggal transfer, dan daftar barang yang dipindahkan
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.transfers.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Gudang Asal & Tujuan</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Dari Gudang Asal (Pengirim)</label>
                        <select name="from_warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Gudang Asal --" required>
                            <option value="">-- Pilih Gudang Asal --</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Ke Gudang Tujuan (Penerima)</label>
                        <select name="to_warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" data-placeholder="-- Pilih Gudang Tujuan --" required>
                            <option value="">-- Pilih Gudang Tujuan --</option>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Transfer</label>
                        <input type="date" name="transfer_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fs-7 fw-semibold text-gray-700">Catatan / Instruksi Logistik</label>
                        <input type="text" name="notes" class="form-control form-control-solid form-control-sm" placeholder="Tujuan mutasi stok, nama driver armada internal, no resi ekspedisi...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Daftar Barang yang Dipindahkan</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-success fw-semibold" onclick="openQuickItemModal(handleTrfNewItem)">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Master Barang
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addTrfRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-2">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="trfTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="width: 60%;">Barang / Produk</th>
                                <th class="text-center" style="width: 30%;">Jumlah Qty Transfer</th>
                                <th class="text-center pe-4" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="trfBody" class="fw-semibold text-gray-700">
                            <tr>
                                <td class="ps-4">
                                    <select name="items[0][item_id]" class="form-select form-select-solid form-select-sm" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach($items as $i)
                                            <option value="{{ $i->id }}">{{ $i->code }} - {{ $i->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-solid form-control-sm text-center" value="1" min="0.01" required>
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeTrfRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.transfers.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Eksekusi Transfer Gudang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let trfIndex = 1;
    function addTrfRow() {
        const tbody = document.getElementById('trfBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4">
                <select name="items[${trfIndex}][item_id]" class="form-select form-select-solid form-select-sm" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}">{{ $i->code }} - {{ $i->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${trfIndex}][quantity]" class="form-control form-control-solid form-control-sm text-center" value="1" min="0.01" required>
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeTrfRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        trfIndex++;
    }

    function handleTrfNewItem(item) {
        document.querySelectorAll('select[name^="items["][name$="[item_id]"]').forEach(select => {
            const opt = new Option(`${item.code} - ${item.name}`, item.id);
            select.add(opt);
        });
        addTrfRow();
    }

    function removeTrfRow(btn) {
        const tbody = document.getElementById('trfBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 baris.');
        }
    }
</script>
@endsection
