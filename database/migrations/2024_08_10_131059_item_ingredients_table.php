<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('ingredient_id')->constrained('ingredients');
            $table->decimal('quantity')->default(0);
            $table->bigInteger('buying_price')->default(0);
            $table->bigInteger('total')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_ingredients');
    }
};
