<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManageController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        $brands = Brand::withCount('products')->orderBy('name')->get();
        
        return view('manage.index', compact('categories', 'brands'));
    }

    public function storeCategory(Request $request)
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

        $validated['name'] = ucwords(strtolower(trim($validated['name'])));
        Category::create($validated);

        return redirect()->route('manage.index')
            ->with('success', 'Category added successfully.');
    }

    public function updateCategory(Request $request, Category $category)
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

        $validated['name'] = ucwords(strtolower(trim($validated['name'])));
        $category->update($validated);

        return redirect()->route('manage.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->route('manage.index')
                ->with('error', 'Cannot delete category. It has products associated with it.');
        }

        $category->delete();

        return redirect()->route('manage.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function storeBrand(Request $request)
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

        $validated['name'] = ucwords(strtolower(trim($validated['name'])));
        Brand::create($validated);

        return redirect()->route('manage.index')
            ->with('success', 'Brand added successfully.');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brand', 'name')->ignore($brand->id),
                'regex:/^[a-zA-Z0-9\s]+$/',
            ],
        ], [
            'name.regex' => 'Brand name can only contain letters, numbers, and spaces.',
        ]);

        $validated['name'] = ucwords(strtolower(trim($validated['name'])));
        $brand->update($validated);

        return redirect()->route('manage.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroyBrand(Brand $brand)
    {
        if ($brand->products()->count() > 0) {
            return redirect()->route('manage.index')
                ->with('error', 'Cannot delete brand. It has products associated with it.');
        }

        $brand->delete();

        return redirect()->route('manage.index')
            ->with('success', 'Brand deleted successfully.');
    }
} 