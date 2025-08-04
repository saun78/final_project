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
            // Add fields to store supplier information when order item is created
            // Note: product_name and product_part_number already exist
            $table->string('supplier_name')->nullable()->after('product_part_number');
            $table->string('supplier_contact_person')->nullable()->after('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_name',
                'supplier_contact_person'
            ]);
        });
    }
};
