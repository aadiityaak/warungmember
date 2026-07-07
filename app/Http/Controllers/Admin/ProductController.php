<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/products/Index', [
            'products' => Product::latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/products/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'points_earned' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil ditambahkan.']);
    }

    public function edit(Product $product): Response
    {
        return inertia('admin/products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'points_earned' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil diperbarui.']);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil dihapus.']);
    }
}
