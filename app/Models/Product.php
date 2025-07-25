<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
=======
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $fillable = [
        'part_number',
        'name',
        'category_id',
        'brand_id',
<<<<<<< HEAD
        'supplier_id', // Required
        'location',
=======
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
        'description',
        'quantity',
        'purchase_price',
        'selling_price',
        'picture',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

<<<<<<< HEAD
    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }



    /**
     * Get the inventory batches for the product.
     */
    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    // Get the category name
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'Unknown';
=======
    // Get the category name
    public function getCategoryNameAttribute()
    {
        $categories = [
            'engine' => 'Engine Parts',
            'electrical' => 'Electrical',
            'body' => 'Body Parts',
            'accessories' => 'Accessories',
            'maintenance' => 'Maintenance',
        ];

        return $categories[$this->category_id] ?? $this->category_id;
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
    }

    // Get the brand name
    public function getBrandNameAttribute()
    {
<<<<<<< HEAD
        return $this->brand ? $this->brand->name : 'Unknown';
    }

    // Get the supplier name
    public function getSupplierNameAttribute()
    {
        return $this->supplier ? $this->supplier->name : 'Unknown';
=======
        $brands = [
            'honda' => 'Honda',
            'yamaha' => 'Yamaha',
            'suzuki' => 'Suzuki',
            'kawasaki' => 'Kawasaki',
        ];

        return $brands[$this->brand_id] ?? $this->brand_id;
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
    }

    // Get stock status
    public function getStockStatusAttribute()
    {
        if ($this->quantity > 10) {
            return 'in_stock';
        } elseif ($this->quantity > 0) {
            return 'low_stock';
        } else {
            return 'out_of_stock';
        }
    }

    // Get stock status badge class
    public function getStockStatusBadgeClassAttribute()
    {
        return [
            'in_stock' => 'success',
            'low_stock' => 'warning',
            'out_of_stock' => 'danger',
        ][$this->stock_status] ?? 'secondary';
    }

    // Get stock status text
    public function getStockStatusTextAttribute()
    {
        return [
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
        ][$this->stock_status] ?? 'Unknown';
    }

    // Get profit margin
    public function getProfitMarginAttribute()
    {
        if ($this->purchase_price == 0) {
            return 0;
        }
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

<<<<<<< HEAD
    // Get total value using batch-based calculation
    public function getTotalValueAttribute()
    {
        // Use batch inventory for accurate valuation
        $totalValue = $this->inventoryBatches()
            ->where('quantity', '>', 0)
            ->get()
            ->sum(function($batch) {
                return $batch->quantity * $batch->purchase_price;
            });
            
        return $totalValue ?: ($this->quantity * $this->purchase_price);
    }

    /**
     * Get real-time quantity from batch inventory
     */
    public function getRealQuantityAttribute(): int
    {
        return ProductInventory::getTotalStock($this->id) ?: $this->quantity;
    }

    /**
     * Get weighted average purchase price from batches
     */
    public function getAveragePurchasePriceAttribute(): float
    {
        return ProductInventory::getAveragePrice($this->id) ?: $this->purchase_price;
    }

    /**
     * Add stock in (create new batch)
     */
    public function addStock($quantity, $purchasePrice, $receivedDate = null, $supplierRef = null, $notes = null, $receiptPhoto = null)
    {
        $batchNo = ProductInventory::generateBatchNo($this->id, $receivedDate);
        
        $batch = ProductInventory::create([
            'product_id' => $this->id,
            'batch_no' => $batchNo,
            'quantity' => $quantity,
            'purchase_price' => $purchasePrice,
            'received_date' => $receivedDate ?: now()->toDateString(),
            'supplier_ref' => $supplierRef,
            'notes' => $notes,
            'receipt_photo' => $receiptPhoto,
        ]);

        // 记录库存移动：进货
        \App\Models\InventoryMovement::recordStockIn(
            $this->id,
            $batch->id,
            $batchNo,
            $quantity,
            $purchasePrice,
            $notes ?: "Stock in for batch {$batchNo}"
        );

        // Product quantity will be automatically synced by ProductInventory model

        return $batch;
    }

    /**
     * Deduct stock using FIFO method
     */
    public function deductStock($quantity)
    {
        $deductions = ProductInventory::deductFIFO($this->id, $quantity);
        
        // Product quantity will be automatically synced by ProductInventory model

        return $deductions;
    }

    /**
     * Update selling price for all future transactions (not affecting cost basis)
     */
    public function updateSellingPriceForAllBatches($newSellingPrice)
    {
        // Only update the main product selling price
        // Batch purchase prices remain unchanged for accurate cost tracking
        $this->update(['selling_price' => $newSellingPrice]);
        
        return $this;
=======
    // Get total value
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->purchase_price;
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
    }
} 