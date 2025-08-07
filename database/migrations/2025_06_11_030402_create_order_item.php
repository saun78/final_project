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
        Schema::create('order_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("order_id");
            $table->unsignedBigInteger("product_id");
            $table->string("product_name")->nullable();
            $table->string("product_part_number")->nullable();
            $table->string("supplier_contact_person")->nullable();
            $table->integer("quantity");
            $table->decimal("price", 10, 2);
            $table->decimal("cost_price", 10, 2)->nullable();
            $table->timestamps();

            // Add foreign key constraint for order (exists before this migration)
            $table->foreign('order_id')->references('id')->on('order')->onDelete('cascade');
            
            // Add foreign key constraint for product_id with set null (preserves order history)
            $table->foreign('product_id')->references('id')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item');
    }
};
