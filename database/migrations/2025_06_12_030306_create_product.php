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
            $table->unsignedBigInteger("supplier_id");
            $table->string("location")->nullable();
            $table->string("description")->nullable();
            $table->string("name");
            $table->integer("quantity");
            $table->decimal("purchase_price", 10, 2)->default(0.00);
            $table->decimal("selling_price", 10, 2)->default(0.00);
            $table->string("picture")->nullable();
            $table->timestamps();

            
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
