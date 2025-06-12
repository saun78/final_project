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
        Schema::table('product', function (Blueprint $table) {
            // Change price columns to decimal
            $table->decimal('purchase_price', 10, 2)->default(0.00)->change();
            $table->decimal('selling_price', 10, 2)->default(0.00)->change();

            // Change ID columns to unsignedBigInteger.
            // IMPORTANT: This assumes the data in these columns can be safely cast to integers.
            // Since we recently reset the database, this should be safe.
            $table->unsignedBigInteger('category_id')->change();
            $table->unsignedBigInteger('brand_id')->change();

            // Add foreign key constraints
            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
        });

        Schema::table('product', function (Blueprint $table) {
            // Revert columns to their original types
            $table->string('category_id')->change();
            $table->string('brand_id')->change();
            $table->integer('purchase_price')->change();
            $table->integer('selling_price')->change();
        });
    }
};
