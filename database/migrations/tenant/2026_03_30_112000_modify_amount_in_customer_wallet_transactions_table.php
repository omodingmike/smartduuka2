<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_wallet_transactions', function (Blueprint $table) {
            $table->decimal('amount', 20 )->change();
            $table->decimal('balance', 20 )->change();
        });
    }

    public function down(): void
    {
        Schema::table('customer_wallet_transactions', function (Blueprint $table) {
            $table->decimal('amount', 8, 2)->change();
            $table->decimal('balance', 8, 2)->change();
        });
    }
};
