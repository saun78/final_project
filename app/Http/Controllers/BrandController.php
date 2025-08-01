<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    /**
     * 清除品牌相关的缓存
     */
    private function clearBrandCache()
    {
        // 清除所有品牌相关的缓存
        cache()->forget('brands_with_count');
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        // 使用缓存键来缓存查询结果（仅对非搜索查询缓存）
        $cacheKey = 'brands_with_count' . ($search ? '_search_' . md5($search) : '');
        
        $brands = cache()->remember($cacheKey, 300, function () use ($search) { // 缓存5分钟
            $query = Brand::withCount('products');
            
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            return $query->orderBy('name')->get();
        });
        
        // 如果是AJAX请求，只返回表格数据
        if ($request->ajax()) {
            return response()->json([
                'brands' => $brands,
                'count' => $brands->count(),
                'search' => $search
            ]);
        }
        
        return view('brands.index', compact('brands'));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:brand,name',
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'Brand name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        Brand::create($validated);

        // 清除缓存
        $this->clearBrandCache();

        return redirect()->route('brands.index')
            ->with('success', 'Brand added successfully.');
    }



    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brand', 'name')->ignore($brand->id),
                'regex:/^[a-zA-Z0-9\s]+$/', // Only letters, numbers, and spaces
            ],
        ], [
            'name.regex' => 'Brand name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        $brand->update($validated);

        // 清除缓存
        $this->clearBrandCache();

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        // 优化：使用exists()而不是count()，性能更好
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')
                ->with('error', 'Cannot delete brand. It has products associated with it.');
        }

        $brand->delete();

        // 清除缓存
        $this->clearBrandCache();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /**
     * Get products for a specific brand
     */
    public function getProducts(Brand $brand)
    {
        $products = $brand->products()
            ->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'brand' => $brand->name,
            'products' => $products
        ]);
    }
} 