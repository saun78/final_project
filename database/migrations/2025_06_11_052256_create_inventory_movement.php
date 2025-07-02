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
        Schema::create('inventory_movement', function (Blueprint $table) {
            $table->id();

            $table->enum('movement_type', ['stock_in', 'sale', 'adjustment', 'transfer']); // 移动类型
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable(); // 批次ID（用于销售记录）
            $table->string('batch_no')->nullable(); // 批次号（冗余存储，便于查询）
            $table->string('reference_type')->nullable(); // 关联类型 (order_item, receipt等)
            $table->unsignedBigInteger('reference_id')->nullable(); // 关联ID
            $table->integer('quantity'); // 数量（正数=入库，负数=出库）
            $table->decimal('unit_cost', 10, 2)->nullable(); // 单位成本
            $table->decimal('total_cost', 10, 2)->nullable(); // 总成本
            $table->unsignedBigInteger('location_id')->nullable(); // 位置ID（如果需要）
            $table->text('notes')->nullable(); // 备注
            $table->timestamp('movement_date'); // 移动时间
            $table->timestamps();
            
            // 外键约束
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
            $table->foreign('batch_id')->references('id')->on('product_inventories')->onDelete('set null');
            $table->foreign('location_id')->references('id')->on('inventory_location')->onDelete('set null');
            
            // 索引
            $table->index(['product_id', 'movement_date']);
            $table->index(['movement_type', 'movement_date']);
            $table->index('batch_id');
            $table->index(['reference_type', 'reference_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement');
    }
};
