<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('commission', 20 )->default(0)->change();
            $table->decimal('commission_paid', 20, 2)->default(0)->change();
            $table->string('department')->nullable()->change();
        });
    }

    public function down(): void
    {
        //
    }
};
