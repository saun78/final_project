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
            $table->string('batch_no'); 
            $table->integer('quantity'); 
            $table->decimal('purchase_price', 10, 2); 
            $table->date('received_date'); 
            $table->string('supplier_ref')->nullable(); 
            $table->text('notes')->nullable(); 
            $table->timestamps();

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