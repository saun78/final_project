<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function getFilteredProducts($search = null, $categoryId = null, $brandId = null, $isSearching = false)
    {
        $query = Product::query();

        // Apply search filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        // If searching, limit to 100 results without pagination
        // If not searching, use normal pagination
        if ($isSearching) {
            return $query->with(['category', 'brand'])->latest()->limit(100)->get();
        } else {
            return $query->with(['category', 'brand'])->latest()->paginate(10);
        }
    }

    public function createProduct(array $data)
    {
        if (isset($data['picture']) && $data['picture']) {
            $data['picture'] = $data['picture']->store('products', 'public');
        }

        return Product::create($data);
    }

    public function updateProduct(Product $product, array $data)
    {
        if (isset($data['picture']) && $data['picture']) {
            // Delete old picture if exists
            if ($product->picture) {
                Storage::disk('public')->delete($product->picture);
            }
            $data['picture'] = $data['picture']->store('products', 'public');
        }

        $product->update($data);
        return $product;
    }

    public function deleteProduct(Product $product)
    {
        // Delete the product's picture if exists
        if ($product->picture) {
            Storage::disk('public')->delete($product->picture);
        }

        return $product->delete();
    }

    public function getFormData()
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ];
    }

    public function getLowStockProducts($threshold = 10)
    {
        return Product::with(['category', 'brand'])
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity', 'asc')
            ->get();
    }

    public function getOutOfStockProducts()
    {
        return Product::with(['category', 'brand'])
            ->where('quantity', 0)
            ->get();
    }
} 