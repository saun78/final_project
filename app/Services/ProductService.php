<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
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

        // Filter by supplier
        if (!empty($filters['supplier'])) {
            $query->where('supplier_id', $filters['supplier']);
        }

        // Filter by location
        if (!empty($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            $statuses = (array)$filters['stock_status'];
            $query->where(function($q) use ($statuses) {
                foreach ($statuses as $status) {
                    if ($status === 'in_stock') {
                        $q->orWhere('quantity', '>', 0);
                    } elseif ($status === 'low_stock') {
                        $q->orWhereBetween('quantity', [1, 10]);
                    } elseif ($status === 'good_stock') {
                        $q->orWhere('quantity', '>', 10);
                    } elseif ($status === 'out_of_stock') {
                        $q->orWhere('quantity', 0);
                    }
                }
            });
        }

        // Filter by price range
        if (!empty($filters['min_price'])) {
            $query->where('selling_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('selling_price', '<=', $filters['max_price']);
        }

        // Check if any filters are applied for pagination
        $isSearching = !empty($filters['search']) || !empty($filters['category']) || 
                       !empty($filters['brand']) || !empty($filters['supplier']) ||
                       !empty($filters['location']) || !empty($filters['stock_status']) || 
                       !empty($filters['min_price']) || !empty($filters['max_price']);

        // Order by stock status (in stock first, out of stock last) then by latest
        $query->orderBy('quantity', 'desc')->latest();
        
        // If searching, limit to 100 results without pagination
        // If not searching, use normal pagination
        if ($isSearching) {
            return $query->with(['category', 'brand', 'supplier'])->limit(100)->get();
        } else {
            return $query->with(['category', 'brand', 'supplier'])->paginate(12);
        }
    }

    public function createProduct(array $data)
    {
        if (isset($data['picture']) && $data['picture']) {
            $data['picture'] = $data['picture']->store('products', 'public');
        }

        $product = Product::create($data);
        
        // Create initial batch if product has initial quantity
        if (isset($data['quantity']) && $data['quantity'] > 0) {
            $batchData = [
                'quantity' => $data['quantity'],
                'purchase_price' => $data['purchase_price'] ?? 0,
                'received_date' => now()->toDateString(),
                'supplier_ref' => null,
                'notes' => 'Initial stock (created with product)',
            ];
            
            $this->addStock($product, $batchData);
        }
        
        return $product;
    }

    public function updateProduct(Product $product, array $data)
    {
        // Store original data for activity logging
        $originalData = $product->getOriginal();
        
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

        // Delete related records explicitly to ensure proper cleanup
        // This is especially important for SQLite which might not handle cascade deletes properly
        $product->inventoryBatches()->delete();
        
        // The foreign key constraints should handle the rest, but we'll be explicit
        // InventoryMovement records will be deleted via cascade
        // OrderItem records will be deleted via cascade (after we add the constraint)

        return $product->delete();
    }

    public function getFormData()
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('contact_person')->get(),
        ];
    }

    public function getStockCounts()
    {
        return [
            'in_stock' => Product::where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::where('quantity', '<=', 0)->count(),
            'total' => Product::count(),
        ];
    }

    public function getLowStockProducts($threshold = 10)
    {
        return Product::with(['category', 'brand', 'supplier'])
            ->where('quantity', '>', 0)
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity', 'asc')
            ->get();
    }

    public function getOutOfStockProducts()
    {
        return Product::with(['category', 'brand', 'supplier'])
            ->where('quantity', 0)
            ->get();
    }

    /**
     * Add stock to product (create new batch)
     */
    public function addStock(Product $product, array $data)
    {
        // Handle receipt photo upload
        $receiptPhotoPath = null;
        if (isset($data['receipt_photo']) && $data['receipt_photo']) {
            $receiptPhotoPath = $data['receipt_photo']->store('receipts', 'public');
        }

        $batch = $product->addStock(
            $data['quantity'],
            $data['purchase_price'],
            $data['received_date'] ?? null,
            $data['supplier_ref'] ?? null,
            $data['notes'] ?? null,
            $receiptPhotoPath
        );

        return $batch;
    }

    /**
     * Get batch inventory for a product (including depleted batches)
     */
    public function getProductBatches($productId)
    {
        return ProductInventory::where('product_id', $productId)
            ->orderBy('received_date', 'asc')
            ->orderBy('batch_no', 'asc')
            ->get();
    }

    /**
     * Get low stock products using batch system
     */
    public function getLowStockProductsWithBatches($threshold = 10)
    {
        // Get products with total batch stock <= threshold
        $products = Product::with(['category', 'brand', 'supplier', 'inventoryBatches'])
            ->get()
            ->filter(function($product) use ($threshold) {
                $totalStock = $product->inventoryBatches->sum('quantity');
                return $totalStock > 0 && $totalStock <= $threshold;
            });

        return $products;
    }
} 