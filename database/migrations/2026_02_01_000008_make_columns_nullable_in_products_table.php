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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_brand_id')->nullable()->change();
            $table->unsignedBigInteger('weight_unit_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('product_brand_id')->nullable(false)->change();
            $table->unsignedBigInteger('weight_unit_id')->nullable(false)->change();
        });
    }
};
