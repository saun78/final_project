<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_item';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'cost_price',
        'product_name',
        'product_part_number',
        'supplier_name',
        'supplier_contact_person'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the product name (from stored data or relationship)
     */
    public function getProductNameAttribute()
    {
        return $this->attributes['product_name'] ?? $this->product?->name ?? 'Unknown Product';
    }

    /**
     * Get the product part number (from stored data or relationship)
     */
    public function getProductPartNumberAttribute()
    {
        return $this->attributes['product_part_number'] ?? $this->product?->part_number;
    }

    /**
     * Get the supplier contact person (from stored data or relationship)
     */
    public function getSupplierContactPersonAttribute()
    {
        return $this->attributes['supplier_contact_person'] ?? $this->product?->supplier?->contact_person ?? 'N/A';
    }

    /**
     * Get the supplier name (from stored data or relationship)
     */
    public function getSupplierNameAttribute()
    {
        return $this->attributes['supplier_name'] ?? $this->product?->supplier?->name ?? 'N/A';
    }

    /**
     * Populate product information fields when product is set
     */
    public function populateProductInfo()
    {
        if ($this->product) {
            $this->product_name = $this->product->name;
            $this->product_part_number = $this->product->part_number;
            $this->supplier_name = $this->product->supplier?->name;
            $this->supplier_contact_person = $this->product->supplier?->contact_person;
            $this->save();
        }
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getProfitAttribute()
    {
        return ($this->price - $this->cost_price) * $this->quantity;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->cost_price == 0) {
            return 0;
        }
        return (($this->price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * 获取该订单项的库存移动记录
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'reference_id')
            ->where('reference_type', 'order_item')
            ->where('movement_type', 'sale');
    }
} 