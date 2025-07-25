<?php

namespace App\Http\Controllers;

use App\Models\Product;
<<<<<<< HEAD
use App\Models\Category;
use App\Models\Brand;
use App\Services\ProductService;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
=======
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
<<<<<<< HEAD
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
        
        return view('products.index', [
            'products' => $products,
            'categories' => $formData['categories'],
            'brands' => $formData['brands'],
            'suppliers' => $formData['suppliers'],
            'isSearching' => $isSearching,
        ]);
=======
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
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
    }

    public function create()
    {
<<<<<<< HEAD
        $formData = $this->productService->getFormData();
        return view('products.create', $formData);
    }

    public function store(ProductStoreRequest $request)
    {
        $this->productService->createProduct($request->validated());
=======
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
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42

        return redirect()->route('products.index')
            ->with('success', 'Part added successfully.');
    }

    public function edit(Product $product)
    {
<<<<<<< HEAD
        $formData = $this->productService->getFormData();
        return view('products.edit', array_merge($formData, ['product' => $product]));
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->productService->updateProduct($product, $request->validated());
=======
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
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42

        return redirect()->route('products.index')
            ->with('success', 'Part updated successfully.');
    }

    public function destroy(Product $product)
    {
<<<<<<< HEAD
        $this->productService->deleteProduct($product);
=======
        // Delete the product's picture if exists
        if ($product->picture) {
            Storage::disk('public')->delete($product->picture);
        }

        $product->delete();
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42

        return redirect()->route('products.index')
            ->with('success', 'Part deleted successfully.');
    }
<<<<<<< HEAD

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
=======
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
} 