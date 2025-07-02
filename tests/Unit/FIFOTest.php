<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FIFOTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_deducts_stock_using_fifo_method()
    {
        // 创建必要的数据
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = \App\Models\Supplier::create([
            'name' => 'Test Supplier',
            'contact_person' => 'John Doe',
            'contact_number' => '123456789',
            'address' => 'Test Address'
        ]);
        
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 0,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
        ]);

        // 添加三个批次
        // 批次1: 10个，价格$8.00，日期最早
        $product->addStock(10, 8.00, '2024-01-01', 'SUP001', 'First batch');
        
        // 批次2: 15个，价格$10.00，日期中间
        $product->addStock(15, 10.00, '2024-01-15', 'SUP002', 'Second batch');
        
        // 批次3: 20个，价格$12.00，日期最新
        $product->addStock(20, 12.00, '2024-02-01', 'SUP003', 'Third batch');

        // 验证总库存
        $this->assertEquals(45, $product->fresh()->quantity);

        // 扣减12个（应该先从第一批次扣减10个，再从第二批次扣减2个）
        $deductions = $product->deductStock(12);

        // 验证扣减记录
        $this->assertCount(2, $deductions);
        
        // 第一个扣减（第一批次：10个 @ $8.00）
        $this->assertEquals(10, $deductions[0]['quantity_deducted']);
        $this->assertEquals(8.00, $deductions[0]['purchase_price']);
        $this->assertEquals(80.00, $deductions[0]['cost']);
        
        // 第二个扣减（第二批次：2个 @ $10.00）
        $this->assertEquals(2, $deductions[1]['quantity_deducted']);
        $this->assertEquals(10.00, $deductions[1]['purchase_price']);
        $this->assertEquals(20.00, $deductions[1]['cost']);

        // 验证批次库存
        $batches = ProductInventory::where('product_id', $product->id)
            ->orderBy('received_date')
            ->get();
            
        $this->assertEquals(0, $batches[0]->quantity);  // 第一批次完全耗尽
        $this->assertEquals(13, $batches[1]->quantity); // 第二批次剩余13个
        $this->assertEquals(20, $batches[2]->quantity); // 第三批次未动

        // 验证产品总库存自动同步
        $this->assertEquals(33, $product->fresh()->quantity);
    }

    /** @test */
    public function it_throws_exception_when_insufficient_stock()
    {
        // 创建必要的数据
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = \App\Models\Supplier::create([
            'name' => 'Test Supplier 2',
            'contact_person' => 'Jane Doe',
            'contact_number' => '987654321',
            'address' => 'Test Address 2'
        ]);
        
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST002',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 0,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
        ]);

        // 只添加5个库存
        $product->addStock(5, 10.00, '2024-01-01', 'SUP001', 'Limited stock');

        // 尝试扣减10个（超过库存）
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('库存不足');
        
        $product->deductStock(10);
    }

    /** @test */
    public function it_calculates_average_cost_correctly()
    {
        // 创建必要的数据
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = \App\Models\Supplier::create([
            'name' => 'Test Supplier 3',
            'contact_person' => 'Bob Smith',
            'contact_number' => '555666777',
            'address' => 'Test Address 3'
        ]);
        
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST003',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 0,
            'purchase_price' => 10.00,
            'selling_price' => 15.00,
        ]);

        // 添加不同价格的批次
        $product->addStock(10, 8.00, '2024-01-01');  // 10 * $8 = $80
        $product->addStock(20, 12.00, '2024-01-02'); // 20 * $12 = $240
        
        // 加权平均成本 = ($80 + $240) / (10 + 20) = $320 / 30 = $10.67
        $avgCost = ProductInventory::getAveragePrice($product->id);
        $this->assertEquals(10.67, round($avgCost, 2));
    }
} 