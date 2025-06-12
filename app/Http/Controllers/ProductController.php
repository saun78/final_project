<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        // Search by part number, name, or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category !== '') {
            $query->where('category_id', $request->category);
        }

        // Filter by brand
        if ($request->has('brand') && $request->brand !== '') {
            $query->where('brand_id', $request->brand);
        }

        $products = $query->latest()->paginate(10);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:50|unique:product',
            'name' => 'required|string|max:255',
            'category_id' => 'required|string|max:50',
            'brand_id' => 'required|string|max:50',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'picture' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('products', 'public');
            $validated['picture'] = $path;
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Part added successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'part_number' => 'required|string|max:50|unique:product,part_number,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|string|max:50',
            'brand_id' => 'required|string|max:50',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'picture' => 'nullable|image|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('picture')) {
            // Delete old picture if exists
            if ($product->picture) {
                Storage::disk('public')->delete($product->picture);
            }
            $path = $request->file('picture')->store('products', 'public');
            $validated['picture'] = $path;
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Part updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Delete the product's picture if exists
        if ($product->picture) {
            Storage::disk('public')->delete($product->picture);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Part deleted successfully.');
    }
} 