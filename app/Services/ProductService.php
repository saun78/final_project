<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function getFilteredProducts($filters = [])
    {
        $query = Product::query();

        // Apply search filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        // Filter by brand
        if (!empty($filters['brand'])) {
            $query->where('brand_id', $filters['brand']);
        }

        // Filter by location
        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $query->where('quantity', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereBetween('quantity', [1, 10]);
                    break;
                case 'good_stock':
                    $query->where('quantity', '>', 10);
                    break;
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
            }
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('selling_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('selling_price', '<=', $filters['max_price']);
        }

        // 总是使用分页，提高性能和用户体验
        return $query->with(['category:id,name', 'brand:id,name'])->latest()->paginate(12);
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
        // 缓存分类和品牌数据，避免重复查询
        $categories = cache()->remember('categories_for_select', 600, function () {
            return Category::orderBy('name')->get(['id', 'name']);
        });

        $brands = cache()->remember('brands_for_select', 600, function () {
            return Brand::orderBy('name')->get(['id', 'name']);
        });

        return [
            'categories' => $categories,
            'brands' => $brands,
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