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
            'supplier',
            'location', 
            'stock_status', 
            'min_price', 
            'max_price'
        ]);

        // Handle special filter parameter
        if ($request->has('filter') && $request->filter === 'low_stock') {
            $filters['stock_status'] = ['low_stock', 'out_of_stock'];
        }

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
                'supplier',
                'location', 
                'stock_status', 
                'min_price', 
                'max_price',
                'filter'
            ]));
        }

        $formData = $this->productService->getFormData();
        
        // Get stock counts for all products (not just current page)
        $stockCounts = $this->productService->getStockCounts();
        
        return view('products.index', [
            'products' => $products,
            'categories' => $formData['categories'],
            'brands' => $formData['brands'],
            'suppliers' => $formData['suppliers'],
            'isSearching' => $isSearching,
            'stockCounts' => $stockCounts,
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

    /**
     * Show stock in form
     */
    public function stockInForm(Product $product)
    {
        return view('products.stock-in', compact('product'));
    }

    /**
     * Process stock in
     */
    public function stockIn(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
            'supplier_ref' => 'nullable|string|max:255',
            'receipt_photo' => 'nullable|image|max:2048', // Max 2MB
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Store original selling price for activity logging
            $originalSellingPrice = $product->selling_price;
            
            // Update selling price if provided
            if ($request->filled('selling_price') && $request->selling_price != $product->selling_price) {
                $product->update(['selling_price' => $request->selling_price]);
            }
            
            // Add the stock batch
            $batch = $this->productService->addStock($product, $request->all());
            
            $message = "Stock added successfully! Batch: {$batch->batch_no}";
            if ($request->filled('selling_price') && $request->selling_price != $originalSellingPrice) {
                $message .= " | Selling price updated to $" . number_format($request->selling_price, 2);
            }
            
            return redirect()->route('products.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to add stock: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show batch inventory for product
     */
    public function batches(Product $product)
    {
        $batches = $this->productService->getProductBatches($product->id);
        
        // Load sales movements for each batch to show order links
        foreach ($batches as $batch) {
            $batch->salesMovements = \App\Models\InventoryMovement::where('batch_id', $batch->id)
                ->where('movement_type', 'sale')
                ->where('reference_type', 'order_item')
                ->with(['product'])
                ->get();
                
            // Get order details for each movement
            $batch->orders = collect();
            foreach ($batch->salesMovements as $movement) {
                if ($movement->reference_id) {
                    $orderItem = \App\Models\OrderItem::with('order')->find($movement->reference_id);
                    if ($orderItem && $orderItem->order) {
                        $batch->orders->push([
                            'order' => $orderItem->order,
                            'quantity_sold' => abs($movement->quantity),
                            'sale_date' => $movement->movement_date,
                            'unit_cost' => $movement->unit_cost,
                            'is_cancelled' => str_contains($movement->notes, '[CANCELLED')
                        ]);
                    }
                }
            }
        }
        
        return view('products.batches', compact('product', 'batches'));
    }

    /**
     * Update selling price for all batches
     */
    public function updateSellingPrice(Request $request, Product $product)
    {
        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product->updateSellingPriceForAllBatches($request->selling_price);

        return redirect()->back()
            ->with('success', 'Selling price updated for all future transactions.');
    }
} 