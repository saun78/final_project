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
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'received_date' => 'date',
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
     * 按FIFO原则扣减库存
     */
    public static function deductFIFO($productId, $quantityToDeduct): array
    {
        $batches = static::getFIFOBatches($productId, $quantityToDeduct);
        $deductions = [];
        $remainingQuantity = $quantityToDeduct;
        
        foreach ($batches as $batch) {
            if ($remainingQuantity <= 0) break;
            
            $deductFromThisBatch = min($remainingQuantity, $batch->quantity);
            
            // 记录扣减信息
            $deductions[] = [
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'quantity_deducted' => $deductFromThisBatch,
                'purchase_price' => $batch->purchase_price,
                'cost' => $deductFromThisBatch * $batch->purchase_price,
            ];
            
            // 更新批次库存
            $batch->quantity -= $deductFromThisBatch;
            $batch->save();
            
            $remainingQuantity -= $deductFromThisBatch;
        }
        
        if ($remainingQuantity > 0) {
            throw new \Exception("库存不足。需要 {$quantityToDeduct}，但只有 " . ($quantityToDeduct - $remainingQuantity) . " 可用。");
        }
        
        return $deductions;
    }

    /**
     * 获取产品的总库存（所有批次的总和）
     */
    public static function getTotalStock($productId): int
    {
        return static::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * 获取产品的加权平均成本
     */
    public static function getAveragePrice($productId): float
    {
        $batches = static::where('product_id', $productId)
            ->where('quantity', '>', 0)
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
}