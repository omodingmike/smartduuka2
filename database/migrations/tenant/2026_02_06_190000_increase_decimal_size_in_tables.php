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
        Schema::table('taxes', function (Blueprint $table) {
            $table->decimal('tax_rate', 20, 6)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('buying_price', 20, 2)->change();
            $table->decimal('selling_price', 20, 2)->change();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('subtotal', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('discount', 20, 2)->change();
            $table->decimal('shipping_charge', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('buying_price', 20, 2)->change();
            $table->decimal('selling_price', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('subtotal', 20, 2)->change();
        });

        Schema::table('stock_taxes', function (Blueprint $table) {
            $table->decimal('tax', 20, 2)->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('discount', 20, 2)->change();
            $table->decimal('shipping_charge', 20, 2)->change();
            $table->decimal('total', 20, 2)->change();
            $table->decimal('balance', 20, 2)->change();
            $table->decimal('delivery_fee', 20, 2)->change();
        });

        Schema::table('damages', function (Blueprint $table) {
            $table->decimal('total', 20, 2)->change();
            $table->decimal('discount', 20, 2)->change();
            $table->decimal('tax', 20, 2)->change();
            $table->decimal('shipping_charge', 20, 2)->change();
        });

        Schema::table('order_purchase_payments', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('credit_deposit_purchases', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('expense_payments', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('production_setups', function (Blueprint $table) {
            $table->decimal('cost', 20, 2)->change();
            $table->decimal('price', 20, 2)->change();
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('commission', 20, 2)->change();
            $table->decimal('commission_paid', 20, 2)->change();
        });

        Schema::table('commission_payouts', function (Blueprint $table) {
            $table->decimal('amount', 20, 2)->change();
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
