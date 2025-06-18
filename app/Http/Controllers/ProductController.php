<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\ProductService;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        // Get all filter parameters
        $filters = $request->only([
            'search', 
            'category', 
            'brand', 
            'location', 
            'stock_status', 
            'min_price', 
            'max_price'
        ]);

        // Remove empty values
        $filters = array_filter($filters, function($value) {
            return $value !== null && $value !== '';
        });

        $products = $this->productService->getFilteredProducts($filters);

        // Check if any filters are applied
        $isSearching = !empty($filters);

        // Append query parameters to pagination links
        if (!$isSearching && method_exists($products, 'appends')) {
            $products->appends($request->only([
                'search', 
                'category', 
                'brand', 
                'location', 
                'stock_status', 
                'min_price', 
                'max_price'
            ]));
        }

        $formData = $this->productService->getFormData();

        return view('products.index', [
            'products' => $products,
            'categories' => $formData['categories'],
            'brands' => $formData['brands'],
            'isSearching' => $isSearching,
        ]);
    }

    public function create()
    {
        $formData = $this->productService->getFormData();
        return view('products.create', $formData);
    }

    public function store(ProductStoreRequest $request)
    {
        $this->productService->createProduct($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Part added successfully.');
    }

    public function edit(Product $product)
    {
        $formData = $this->productService->getFormData();
        return view('products.edit', array_merge($formData, ['product' => $product]));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Part updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->productService->deleteProduct($product);

        return redirect()->route('products.index')
            ->with('success', 'Part deleted successfully.');
    }
} 