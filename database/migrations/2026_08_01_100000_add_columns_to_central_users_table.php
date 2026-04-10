<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('commission', 20, 2)->default(0);
            $table->decimal('commission_paid', 20, 2)->default(0);
            $table->string('phone2')->nullable()->unique();
            $table->string('type')->nullable();
            $table->string('notes')->nullable();
            $table->timestamp('last_login_date')->nullable();
            $table->string('department')->nullable();
            $table->boolean('force_reset')->default(false);
            $table->string('tenant_id')->nullable();
            $table->boolean('is_reset')->default(false);
            $table->string('raw_pin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'commission',
                'commission_paid',
                'phone2',
                'type',
                'notes',
                'last_login_date',
                'department',
                'force_reset',
                'tenant_id',
                'is_reset',
                'raw_pin',
            ]);
        });
    }
};
