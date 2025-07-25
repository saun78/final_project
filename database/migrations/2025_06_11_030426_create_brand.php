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
        Schema::create('brand', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            $table->string("name")->unique();
            $table->timestamps();
            
            // Add index for performance
            $table->index('name');
=======
            $table->string("name");
            $table->timestamps();
>>>>>>> 19642a44c7f4ce1bcfbd31954f4a18b7e34fea42
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand');
    }
};
