<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->decimal('price',20)->default( 0);
        });
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
