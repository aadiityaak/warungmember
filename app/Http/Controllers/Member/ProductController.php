<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        return inertia('member/products/Index', [
            'products' => Product::with('categories')
                ->where('is_active', true)
                ->latest()
                ->get(),
            'categories' => ProductCategory::orderBy('name')->get(),
        ]);
    }
}
