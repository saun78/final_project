<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProductInventory extends Model
{
    protected $fillable = [
        'product_id',
        'batch_no',
        'quantity',
        'purchase_price',
        'received_date',
        'supplier_ref',
        'notes',
        'receipt_photo',
        'status',
        'depleted_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'received_date' => 'date',
        'depleted_date' => 'date',
    ];

    /**
     * Boot method to sync product quantity on inventory changes
     */
    protected static function boot()
    {
        parent::boot();
        
        // Sync product quantity after creating/updating/deleting inventory
        static::saved(function ($inventory) {
            $inventory->syncProductQuantity();
        });
        
        static::deleted(function ($inventory) {
            $inventory->syncProductQuantity();
        });
    }

    /**
     * Sync the parent product's quantity with batch totals
     */
    private function syncProductQuantity()
    {
        if ($this->product_id) {
            $totalQuantity = static::getTotalStock($this->product_id);
            Product::where('id', $this->product_id)->update(['quantity' => $totalQuantity]);
        }
    }

    /**
     * 关联产品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 生成批次号
     * 格式: YYYYMMDD-XXX (日期-序号)
     */
    public static function generateBatchNo($productId, $date = null): string
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $date->format('Ymd');
        
        // 查找当天该产品的最大序号
        $lastBatch = static::where('product_id', $productId)
            ->where('batch_no', 'like', $dateStr . '-%')
            ->orderBy('batch_no', 'desc')
            ->first();
            
        if ($lastBatch) {
            $lastSequence = (int) substr($lastBatch->batch_no, -3);
            $sequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }
        
        return $dateStr . '-' . $sequence;
    }

    /**
     * 获取指定产品的FIFO库存批次
     */
    public static function getFIFOBatches($productId, $requiredQuantity = null)
    {
        $query = static::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->where('status', 'active')
            ->orderBy('received_date', 'asc')
            ->orderBy('batch_no', 'asc');
            
        if ($requiredQuantity) {
            // 如果指定了需要的数量，返回足够满足需求的批次
            $batches = $query->get();
            $selectedBatches = collect();
            $remainingQuantity = $requiredQuantity;
            
            foreach ($batches as $batch) {
                if ($remainingQuantity <= 0) break;
                
                $selectedBatches->push($batch);
                $remainingQuantity -= $batch->quantity;
            }
            
            return $selectedBatches;
        }
        
        return $query->get();
    }

    /**
     * 按FIFO原则扣减库存，并记录批次使用情况
     */
    public static function deductFIFO($productId, $quantityToDeduct, $orderItemId = null): array
    {
        $batches = static::getFIFOBatches($productId, $quantityToDeduct);
        $deductions = [];
        $remainingQuantity = $quantityToDeduct;
        
        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) break;
            
            $deductFromThisBatch = min($remainingQuantity, $batch->quantity);
            $originalQuantity = $batch->quantity;
            
            // 记录扣减信息
            $deductions[] = [
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'quantity_deducted' => $deductFromThisBatch,
                'purchase_price' => $batch->purchase_price,
                'cost' => $deductFromThisBatch * $batch->purchase_price,
            ];
            
            // 如果提供了订单项ID，记录销售移动
            if ($orderItemId) {
                \App\Models\InventoryMovement::recordSale(
                    $batch->product_id,
                    $batch->id,
                    $batch->batch_no,
                    $orderItemId,
                    $deductFromThisBatch,
                    $batch->purchase_price
                );
            }
            
            // 更新批次库存
            $batch->quantity -= $deductFromThisBatch;
            
            // 如果批次用完，标记为已用完
            if ($batch->quantity == 0) {
                $batch->status = 'depleted';
                $batch->depleted_date = now()->toDateString();
            }
            
            $batch->save();
            
            $remainingQuantity -= $deductFromThisBatch;
        }
        
        if ($remainingQuantity > 0) {
            throw new \Exception("库存不足。需要 {$quantityToDeduct}，但只有 " . ($quantityToDeduct - $remainingQuantity) . " 可用。");
        }
        
        return $deductions;
    }



    /**
     * 获取产品的总库存（所有活跃批次的总和）
     */
    public static function getTotalStock($productId): int
    {
        return static::where('product_id', $productId)
            ->where('status', 'active')
            ->sum('quantity');
    }

    /**
     * 获取产品的加权平均成本
     */
    public static function getAveragePrice($productId): float
    {
        $batches = static::where('product_id', $productId)
            ->where('quantity', '>', 0)
            ->where('status', 'active')
            ->get();
            
        if ($batches->isEmpty()) {
            return 0;
        }
        
        $totalValue = $batches->sum(function($batch) {
            return $batch->quantity * $batch->purchase_price;
        });
        
        $totalQuantity = $batches->sum('quantity');
        
        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }


    /**
     * 获取照片URL
     */
    public function getReceiptPhotoUrlAttribute()
    {
        return $this->receipt_photo ? asset('storage/' . $this->receipt_photo) : null;
    }

    /**
     * 获取该批次的库存移动记录
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class, 'batch_id');
    }
}

