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

            // 外键约束
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            
            // 索引
            $table->index(['product_id', 'received_date']); // 用于FIFO查询
            $table->index('batch_no');
            $table->unique(['product_id', 'batch_no']); // 确保同一产品的批次号唯一
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
