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
<<<<<<< HEAD
            $table->unsignedBigInteger("order_id");
            $table->unsignedBigInteger("product_id");
            $table->integer("quantity");
            $table->decimal("price", 10, 2);
            $table->decimal("cost_price", 10, 2)->nullable();
            $table->timestamps();

            // Add foreign key constraint for order (exists before this migration)
            $table->foreign('order_id')->references('id')->on('`order`')->onDelete('cascade');
=======
            $table->string("order_id");
            $table->string("product_id");
            $table->integer("quantity");
            $table->integer("price");
            $table->timestamps();
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
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
