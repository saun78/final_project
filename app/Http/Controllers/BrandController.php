<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
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

        return redirect()->route('brands.index')
            ->with('success', 'Brand added successfully.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
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

        return redirect()->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->count() > 0) {
            return redirect()->route('brands.index')
                ->with('error', 'Cannot delete brand. It has products associated with it.');
        }

        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
} 