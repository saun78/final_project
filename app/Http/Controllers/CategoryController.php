<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * 清除分类相关的缓存
     */
    private function clearCategoryCache()
    {
        // 清除所有分类相关的缓存
        cache()->forget('categories_with_count');
        // 也可以使用标签来清除所有相关缓存（如果使用支持标签的缓存驱动）
        // cache()->tags(['categories'])->flush();
    }
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        // 使用缓存键来缓存查询结果（仅对非搜索查询缓存）
        $cacheKey = 'categories_with_count' . ($search ? '_search_' . md5($search) : '');
        
        $categories = cache()->remember($cacheKey, 300, function () use ($search) { // 缓存5分钟
            $query = Category::withCount('products');
            
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            return $query->orderBy('name')->get();
        });
        
        // 如果是AJAX请求，只返回表格数据
        if ($request->ajax()) {
            return response()->json([
                'categories' => $categories,
                'count' => $categories->count(),
                'search' => $search
            ]);
        }
        
        return view('categories.index', compact('categories'));
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:category,name',
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'Category name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        Category::create($validated);

        // 清除缓存
        $this->clearCategoryCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category added successfully.');
    }



    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'name')->ignore($category->id),
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'Category name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        $category->update($validated);

        // 清除缓存
        $this->clearCategoryCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // 优化：使用exists()而不是count()，性能更好
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category. It has products associated with it.');
        }

        $category->delete();

        // 清除缓存
        $this->clearCategoryCache();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
} 