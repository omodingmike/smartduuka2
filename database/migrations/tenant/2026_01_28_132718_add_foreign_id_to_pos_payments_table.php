<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->change();
            $table->dateTime('date')->change();
            $table->renameColumn('payment_method', 'payment_method_id');
        });

        // Use a raw statement to handle the type conversion for Postgres
        DB::statement('ALTER TABLE pos_payments ALTER COLUMN payment_method_id TYPE BIGINT USING payment_method_id::bigint');

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_method_id')->nullable()->change();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['payment_method_id']);
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->renameColumn('payment_method_id', 'payment_method');
        });

        // Revert back to string using raw SQL if necessary
        DB::statement('ALTER TABLE pos_payments ALTER COLUMN payment_method TYPE VARCHAR(255)');

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->change();
            $table->date('date')->change();
        });
    }
};
