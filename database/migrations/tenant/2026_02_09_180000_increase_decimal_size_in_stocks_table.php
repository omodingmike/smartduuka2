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
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('price', 20, 2)->default(0)->change();
            $table->decimal('quantity', 20, 2)->default(1)->change();
            $table->decimal('discount', 20, 2)->default(0)->change();
            $table->decimal('subtotal', 20, 2)->change();
            $table->decimal('total', 20, 6)->default(0)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('other_quantity', 20, 2)->default(0)->change();
            $table->decimal('delivery', 20, 2)->default(0)->change();
            $table->decimal('rate', 20, 2)->nullable()->change();
            $table->decimal('purchase_quantity', 20, 2)->default(0)->change();
            $table->decimal('system_stock', 20, 2)->default(0)->change();
            $table->decimal('physical_stock', 20, 2)->default(0)->change();
            $table->decimal('quantity_ordered', 20, 2)->default(0)->change();
            $table->decimal('quantity_received', 20, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
