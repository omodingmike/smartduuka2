<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'pre_order_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->unsignedTinyInteger('pre_order_status')->nullable()->after('warehouse_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'pre_order_status')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('pre_order_status');
            });
        }
    }
};
