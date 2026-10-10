<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::withCount('items');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }

        $brands = $query->orderBy('name')->paginate(15);

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:brands,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Brand::create([
            'code' => $request->code ? strtoupper($request->code) : ('BRD-' . strtoupper(substr(uniqid(), -5))),
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('brands.index')->with('success', 'Merk / Brand berhasil ditambahkan.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'code' => ['nullable', 'string', 'max:50', Rule::unique('brands')->ignore($brand->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $brand->update([
            'code' => $request->code ? strtoupper($request->code) : $brand->code,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('brands.index')->with('success', 'Data Merk / Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->items()->count() > 0) {
            return back()->with('error', 'Merk tidak dapat dihapus karena masih digunakan pada data master barang.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Merk / Brand berhasil dihapus.');
    }
}
