<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
<<<<<<< HEAD:database/migrations/2025_06_25_022707_create_product_inventories_table.php
            $table->string('batch_no'); 
            $table->integer('quantity'); 
            $table->decimal('purchase_price', 10, 2); 
            $table->date('received_date'); 
            $table->string('supplier_ref')->nullable(); 
            $table->text('notes')->nullable(); 
            $table->timestamps();   
=======
            $table->string('batch_no'); // 批次号，使用日期格式 YYYYMMDD-XXX
            $table->integer('quantity'); // 此批次的库存数量
            $table->decimal('purchase_price', 10, 2); // 此批次的采购价格
            $table->date('received_date'); // 收货日期（用于FIFO排序）
            $table->string('supplier_ref')->nullable(); // 供应商参考号
            $table->text('notes')->nullable(); // 备注
            $table->string('receipt_photo')->nullable(); // 进货单据照片
            $table->enum('status', ['active', 'depleted'])->default('active'); // 批次状态
            $table->date('depleted_date')->nullable(); // 用完日期
            $table->timestamps();
>>>>>>> cd5cafca600346b2d6f1d834f77d5c6bd80c2e98:database/migrations/2025_06_24_024441_create_product_inventories_table.php

            // 外键约束
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            
            // 索引
            $table->index(['product_id', 'received_date']); 
            $table->index('batch_no');
            $table->unique(['product_id', 'batch_no']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_inventories');
    }
};