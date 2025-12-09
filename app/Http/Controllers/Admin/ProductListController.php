<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductListController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['import', 'creator', 'updater']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        // Filter by status (only if a value is selected)
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->get('is_active'));
        }

        $products = $query->latest()->paginate(25);
        $categories = Product::distinct()->pluck('category')->filter();

        return view('admin.products.simple-list', compact('products', 'categories'));
    }


    public function show($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Product::distinct()->pluck('category')->filter();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_arabic' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'category_arabic' => 'nullable|string|max:100',
            'type_english' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'description_arabic' => 'nullable|string',
            'manufacturer' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:50',
            'shelf_life' => 'nullable|string|max:100',
        ]);

        $product->update($validated);

        return redirect()
            ->route('admin.products-list.show', $product)
            ->with('success', 'Product updated successfully!');
    }
}
