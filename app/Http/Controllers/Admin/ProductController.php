<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/products/Index', [
            'products' => Product::with('categories')->latest()->get(),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/products/Create', [
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price' => 'required|integer|min:0',
            'discount_price' => 'nullable|integer|min:0|lt:price',
            'discount_end_at' => 'nullable|date|after:now',
            'points_earned' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:product_categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = Storage::url(
                $request->file('image')->store('products', 'public')
            );
        }

        $product = Product::create($validated);

        if (! empty($validated['categories'])) {
            $product->categories()->sync($validated['categories']);
        }

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil ditambahkan.']);
    }

    public function edit(Product $product): Response
    {
        $product->load('categories');

        return inertia('admin/products/Edit', [
            'product' => $product,
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'price' => 'required|integer|min:0',
            'discount_price' => 'nullable|integer|min:0|lt:price',
            'discount_end_at' => 'nullable|date|after:now',
            'points_earned' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:product_categories,id',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                $oldPath = str_replace('/storage/', '', $product->image);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $validated['image'] = Storage::url(
                $request->file('image')->store('products', 'public')
            );
        }

        $product->update($validated);

        $product->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil diperbarui.']);
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            $oldPath = str_replace('/storage/', '', $product->image);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('toast', ['type' => 'success', 'message' => 'Produk berhasil dihapus.']);
    }
}
