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
            // Add missing product information columns
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('product_part_number')->nullable()->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'product_part_number'
            ]);
        });
    }
};
