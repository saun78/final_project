<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $fillable = [
        'part_number',
        'name',
        'category_id',
        'brand_id',
        'location',
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

    // Get the category name
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'Unknown';
    }

    // Get the brand name
    public function getBrandNameAttribute()
    {
        return $this->brand ? $this->brand->name : 'Unknown';
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

    // Get total value
    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->purchase_price;
    }
} 