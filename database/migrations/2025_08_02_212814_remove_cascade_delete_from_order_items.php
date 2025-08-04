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
        Schema::table('order_item', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Make product_id nullable
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            // Recreate the foreign key constraint without cascade delete
            $table->foreign('product_id')->references('id')->on('product')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Recreate the foreign key constraint with cascade delete
            $table->foreign('product_id')->references('id')->on('product')->onDelete('cascade');
        });
    }
};
