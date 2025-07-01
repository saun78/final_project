<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $table = 'inventory_movement';

    protected $fillable = [
        'movement_type',
        'product_id',
        'batch_id',
        'batch_no',
        'reference_type',
        'reference_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'location_id',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    /**
     * 关联产品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 关联批次
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductInventory::class, 'batch_id');
    }

    /**
     * 关联位置（暂时未实现）
     */
    // public function location(): BelongsTo
    // {
    //     return $this->belongsTo(InventoryLocation::class, 'location_id');
    // }

    /**
     * 记录库存移动
     */
    public static function recordMovement(array $data): self
    {
        return static::create(array_merge($data, [
            'movement_date' => now(),
        ]));
    }

    /**
     * 记录销售移动
     */
    public static function recordSale($productId, $batchId, $batchNo, $orderItemId, $quantity, $unitCost): self
    {
        return static::recordMovement([
            'movement_type' => 'sale',
            'product_id' => $productId,
            'batch_id' => $batchId,
            'batch_no' => $batchNo,
            'reference_type' => 'order_item',
            'reference_id' => $orderItemId,
            'quantity' => -$quantity, // 负数表示出库
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'notes' => "Sale from batch {$batchNo}",
        ]);
    }

    /**
     * 记录入库移动
     */
    public static function recordStockIn($productId, $batchId, $batchNo, $quantity, $unitCost, $notes = null): self
    {
        return static::recordMovement([
            'movement_type' => 'stock_in',
            'product_id' => $productId,
            'batch_id' => $batchId,
            'batch_no' => $batchNo,
            'quantity' => $quantity, // 正数表示入库
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'notes' => $notes ?: "Stock in for batch {$batchNo}",
        ]);
    }

    /**
     * 记录库存返回移动（订单取消时的库存恢复）
     */
    public static function recordReturn($productId, $batchId, $batchNo, $quantity, $unitCost, $orderNumber): self
    {
        return static::recordMovement([
            'movement_type' => 'adjustment',
            'product_id' => $productId,
            'batch_id' => $batchId,
            'batch_no' => $batchNo,
            'reference_type' => 'order_cancellation',
            'reference_id' => null, // Could store order ID if needed
            'quantity' => $quantity, // 正数表示返回库存
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'notes' => "Stock returned to original batch {$batchNo} from cancelled order #{$orderNumber}",
        ]);
    }

    /**
     * 获取特定订单项的批次使用记录
     */
    public static function getOrderItemBatches($orderItemId)
    {
        return static::where('movement_type', 'sale')
            ->where('reference_type', 'order_item')
            ->where('reference_id', $orderItemId)
            ->get();
    }

    /**
     * 获取产品的库存移动历史
     */
    public static function getProductMovements($productId, $limit = null)
    {
        $query = static::where('product_id', $productId)
            ->orderBy('movement_date', 'desc');
            
        if ($limit) {
            return $query->limit($limit)->get();
        }
        
        return $query->get();
    }
} 