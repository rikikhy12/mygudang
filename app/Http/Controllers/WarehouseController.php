<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::with(['branch'])->withCount('stockLedgers')->get();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();

        return view('warehouses.create', compact('branches'));
    }

    public function edit(Warehouse $warehouse)
    {
        $branches = \App\Models\Branch::where('is_active', true)->orderByDesc('is_main')->orderBy('name')->get();

        return view('warehouses.edit', compact('warehouse', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', 'unique:warehouses,code'],
            'name' => ['required', 'string'],
            'location' => ['nullable', 'string'],
        ]);

        Warehouse::create([
            'branch_id' => $request->branch_id,
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'location' => $request->location,
            'is_active' => true,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'branch_id' => ['nullable', 'exists:branches,id'],
            'code' => ['required', 'string', Rule::unique('warehouses')->ignore($warehouse->id)],
            'name' => ['required', 'string'],
            'location' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $warehouse->update([
            'branch_id' => $request->branch_id,
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'location' => $request->location,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Data gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stockLedgers()->exists()) {
            return back()->with('error', 'Gudang ini telah memiliki transaksi mutasi stok dan tidak dapat dihapus.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Gudang berhasil dihapus.');
    }
}
