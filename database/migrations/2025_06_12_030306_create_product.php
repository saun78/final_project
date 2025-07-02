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
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string("part_number")->nullable();
            $table->unsignedBigInteger("category_id");
            $table->unsignedBigInteger("brand_id");
<<<<<<< HEAD:database/migrations/2025_06_12_030306_create_product.php
            $table->unsignedBigInteger("supplier_id")->nullable();
=======
            $table->unsignedBigInteger("supplier_id");
>>>>>>> cd5cafca600346b2d6f1d834f77d5c6bd80c2e98:database/migrations/2025_06_11_030500_create_product.php
            $table->string("location")->nullable();
            $table->string("description")->nullable();
            $table->string("name");
            $table->integer("quantity");
            $table->decimal("purchase_price", 10, 2)->default(0.00);
            $table->decimal("selling_price", 10, 2)->default(0.00);
            $table->string("picture")->nullable();
            $table->timestamps();

            // Add foreign key constraints
<<<<<<< HEAD:database/migrations/2025_06_12_030306_create_product.php
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('set null');
=======
            $table->foreign('category_id')->references('id')->on('category')->onDelete('restrict');
            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('supplier')->onDelete('restrict');
            
            // Add indexes for performance
            $table->index('part_number');
            $table->index('name');
            $table->index(['category_id', 'brand_id']);
            $table->index('quantity');
        });

        // Add foreign key constraint for order_item table after product table is created
        Schema::table('order_item', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
>>>>>>> cd5cafca600346b2d6f1d834f77d5c6bd80c2e98:database/migrations/2025_06_11_030500_create_product.php
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraint from order_item table first
        Schema::table('order_item', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        
        Schema::dropIfExists('product');
    }
};