<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:category,name',
                'regex:/^[a-zA-Z0-9\s]+$/', // Only letters, numbers, and spaces
            ],
        ], [
            'name.regex' => 'Category name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category added successfully.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'name')->ignore($category->id),
                'regex:/^[a-zA-Z0-9\s]+$/', // Only letters, numbers, and spaces
            ],
        ], [
            'name.regex' => 'Category name can only contain letters, numbers, and spaces.',
        ]);

        // Remove extra spaces and convert to proper case
        $validated['name'] = ucwords(strtolower(trim($validated['name'])));

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category. It has products associated with it.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
} 