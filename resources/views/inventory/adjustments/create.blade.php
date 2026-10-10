@extends('layouts.app')

@section('title', 'Buat Penyesuaian Stok')
@section('page_title', 'Formulir Penyesuaian Stok (Stock Opname)')

@section('content')
<div class="d-flex flex-column gap-4 w-100">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h2 class="fs-4 fw-bold text-gray-900 mb-1">Stock Opname & Penyesuaian Stok</h2>
            <p class="text-muted fs-7 mb-0">Sesuaikan selisih kuantitas fisik gudang nyata dengan saldo buku sistem</p>
        </div>
        <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-sm btn-light-primary fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Opname
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header border-bottom py-4 px-6">
            <div class="card-title d-flex flex-column align-items-start m-0">
                <h3 class="fw-bold text-gray-800 m-0 fs-5 d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-primary fs-3"></i> Formulir Penyesuaian Stok (Stock Opname)
                </h3>
                <span class="text-muted fs-7 fw-normal mt-1">
                    Silakan tentukan gudang lokasi opname, tanggal penyesuaian, dan input hasil hitung fisik nyata
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.adjustments.store') }}">
            @csrf
            
            <div class="card-body p-6">
                <!-- Section 1 -->
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="badge badge-circle badge-primary fs-7 fw-bold">1</span>
                    <h4 class="fs-6 fw-bold text-gray-800 m-0">Informasi Lokasi & Tanggal Opname</h4>
                </div>

                <div class="row g-3 mb-5">
                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Lokasi Gudang Opname</label>
                        <select name="warehouse_id" class="form-select form-select-solid form-select-sm" data-control="select2" required>
                            @foreach($warehouses as $w)
                                <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Tanggal Penyesuaian</label>
                        <input type="date" name="adjustment_date" class="form-control form-control-solid form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fs-7 fw-semibold text-gray-700 required">Alasan Penyesuaian</label>
                        <input type="text" name="reason" class="form-control form-control-solid form-control-sm" required placeholder="Contoh: Hasil Opname Bulanan, Barang Rusak...">
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-circle badge-primary fs-7 fw-bold">2</span>
                        <h4 class="fs-6 fw-bold text-gray-800 m-0">Daftar Barang & Hasil Hitung Fisik (Real Count)</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-light-success fw-semibold" onclick="openQuickItemModal(handleAdjNewItem)">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Master Barang
                        </button>
                        <button type="button" class="btn btn-sm btn-primary fw-semibold" onclick="addAdjRow()">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris
                        </button>
                    </div>
                </div>

                <div class="table-responsive rounded border mb-4">
                    <table class="table table-row-dashed table-row-gray-200 align-middle gs-4 gy-3 fs-7 mb-0 no-datatable" id="adjTable">
                        <thead>
                            <tr class="fw-bold text-muted text-uppercase fs-8 bg-light text-nowrap">
                                <th class="ps-4 py-3" style="width: 55%;">Barang / Produk</th>
                                <th class="text-center" style="width: 35%;">Jumlah Fisik Sebenarnya (Real Count)</th>
                                <th class="text-center pe-4" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="adjBody" class="fw-semibold text-gray-700">
                            <tr>
                                <td class="ps-4">
                                    <select name="items[0][item_id]" class="form-select form-select-solid form-select-sm" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach($items as $i)
                                            <option value="{{ $i->id }}">{{ $i->code }} - {{ $i->name }} (Sistem: {{ number_format($i->current_stock, 0) }} {{ $i->unit->symbol }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][physical_stock]" class="form-control form-control-solid form-control-sm text-center" value="0" min="0" required placeholder="Fisik nyata...">
                                </td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeAdjRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-info d-flex align-items-center p-4 mb-2 rounded-3 fs-7">
                    <i class="bi bi-info-circle-fill fs-2 text-info me-3"></i>
                    <div class="text-gray-800">
                        Sistem akan otomatis menghitung selisih kuantitas, memperbarui kartu stok, dan memposting jurnal penyesuaian persediaan jika terjadi selisih nilai nominal.
                    </div>
                </div>
            </div>

            <div class="card-footer bg-transparent border-top py-3 px-6 d-flex justify-content-end gap-2">
                <a href="{{ route('inventory.adjustments.index') }}" class="btn btn-sm btn-light">Batal</a>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-check2-circle me-1"></i> Posting Penyesuaian Stok
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let adjIndex = 1;
    function addAdjRow() {
        const tbody = document.getElementById('adjBody');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-4">
                <select name="items[${adjIndex}][item_id]" class="form-select form-select-solid form-select-sm" required>
                    <option value="">-- Pilih Barang --</option>
                    @foreach($items as $i)
                        <option value="{{ $i->id }}">{{ $i->code }} - {{ $i->name }} (Sistem: {{ number_format($i->current_stock, 0) }} {{ $i->unit->symbol }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${adjIndex}][physical_stock]" class="form-control form-control-solid form-control-sm text-center" value="0" min="0" required>
            </td>
            <td class="text-center pe-4">
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="removeAdjRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        adjIndex++;
    }

    function handleAdjNewItem(item) {
        document.querySelectorAll('select[name^="items["][name$="[item_id]"]').forEach(select => {
            const opt = new Option(`${item.code} - ${item.name} (${item.unit_symbol || 'pcs'})`, item.id);
            select.add(opt);
        });
        addAdjRow();
    }

    function removeAdjRow(btn) {
        const tbody = document.getElementById('adjBody');
        if (tbody.children.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('Minimal harus ada 1 baris.');
        }
    }
</script>
@endsection
