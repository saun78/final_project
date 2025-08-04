<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SoftDeleteProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    public function test_soft_deleted_products_are_excluded_from_reports()
    {
        // Create test data
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = Supplier::create([
            'contact_person' => 'Test Supplier',
            'contact_number' => '123456789'
        ]);

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 10,
            'purchase_price' => 50.00,
            'selling_price' => 100.00
        ]);

        // Create an order with the product
        $order = Order::create([
            'order_number' => 'ORD001',
            'payment_method' => 'cash',
            'amount' => 100.00
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00,
            'cost_price' => 50.00,
            'product_name' => $product->name,
            'product_part_number' => $product->part_number,
            'supplier_name' => $supplier->contact_person,
            'supplier_contact_person' => $supplier->contact_person
        ]);

        // Verify product appears in reports before deletion
        $response = $this->get('/reports/top-selling');
        $response->assertStatus(200);
        $response->assertSee($product->name);

        // Soft delete the product
        $product->delete();

        // Verify product is excluded from top-selling reports after deletion
        $response = $this->get('/reports/top-selling');
        $response->assertStatus(200);
        $response->assertDontSee($product->name);

        // Verify product is excluded from profit reports after deletion
        $response = $this->get('/reports/profit');
        $response->assertStatus(200);
        $response->assertDontSee($product->name);

        // Verify product still appears in summary reports after deletion
        $response = $this->get('/reports/summary');
        $response->assertStatus(200);
        $response->assertSee('100.00'); // Should show the total amount from the order

        // Verify product still exists in database (soft deleted)
        $this->assertDatabaseHas('product', [
            'id' => $product->id,
        ]);
        $this->assertNotNull(Product::withTrashed()->find($product->id)->deleted_at);
    }

    public function test_receipts_still_show_deleted_products()
    {
        // Create test data
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = Supplier::create([
            'contact_person' => 'Test Supplier',
            'contact_number' => '123456789'
        ]);

        // Create a product
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 10,
            'purchase_price' => 50.00,
            'selling_price' => 100.00
        ]);

        // Create an order with the product
        $order = Order::create([
            'order_number' => 'ORD001',
            'payment_method' => 'cash',
            'amount' => 100.00
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100.00,
            'cost_price' => 50.00,
            'product_name' => $product->name,
            'product_part_number' => $product->part_number,
            'supplier_name' => $supplier->contact_person,
            'supplier_contact_person' => $supplier->contact_person
        ]);

        // Soft delete the product
        $product->delete();

        // Verify receipt still shows the product information
        $response = $this->get("/orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->part_number);
    }

    public function test_deleted_order_shows_stock_restoration_in_movements()
    {
        // Create test data
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = Supplier::create([
            'contact_person' => 'Test Supplier',
            'contact_number' => '123456789'
        ]);

        // Create a product with initial stock
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 10,
            'purchase_price' => 50.00,
            'selling_price' => 100.00
        ]);

        // Create initial batch for the product
        $batch = $product->addStock(
            10,
            50.00,
            now()->toDateString(),
            'TEST-BATCH-001',
            'Initial test batch'
        );

        // Verify initial stock level
        $this->assertEquals(10, $product->fresh()->quantity);

        // Create an order using the proper store method
        $orderData = [
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->post('/orders', $orderData);
        $response->assertRedirect(route('orders.index'));

        // Verify stock was deducted
        $this->assertEquals(9, $product->fresh()->quantity);

        // Get the created order
        $order = Order::where('payment_method', 'cash')->latest()->first();
        $this->assertNotNull($order);

        // Delete the order (which should restore stock)
        $response = $this->delete("/orders/{$order->id}");
        $response->assertRedirect(route('orders.index'));

        // Verify stock is restored
        $this->assertEquals(10, $product->fresh()->quantity);

        // Check dashboard to see if restoration movement is shown
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Restored'); // Should show the restoration badge
    }

    public function test_deleted_products_excluded_from_recent_movements()
    {
        // Create test data
        $category = Category::create(['name' => 'Test Category']);
        $brand = Brand::create(['name' => 'Test Brand']);
        $supplier = Supplier::create([
            'contact_person' => 'Test Supplier',
            'contact_number' => '123456789'
        ]);

        // Create a product with initial stock
        $product = Product::create([
            'name' => 'Test Product',
            'part_number' => 'TEST001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'supplier_id' => $supplier->id,
            'quantity' => 10,
            'purchase_price' => 50.00,
            'selling_price' => 100.00
        ]);

        // Create initial batch for the product
        $batch = $product->addStock(
            10,
            50.00,
            now()->toDateString(),
            'TEST-BATCH-001',
            'Initial test batch'
        );

        // Verify product appears in recent movements before deletion
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee($product->name);

        // Soft delete the product
        $product->delete();

        // Verify product is excluded from recent movements after deletion
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee($product->name);

        // Verify product still exists in database (soft deleted)
        $this->assertDatabaseHas('product', [
            'id' => $product->id,
        ]);
        $this->assertNotNull(Product::withTrashed()->find($product->id)->deleted_at);
    }
}
