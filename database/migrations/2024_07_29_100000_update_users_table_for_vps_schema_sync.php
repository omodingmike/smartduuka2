<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_reset')) {
                $table->boolean('is_reset')->default(FALSE)->after('global_id');
            }
            if (!Schema::hasColumn('users', 'raw_pin')) {
                $table->string('raw_pin')->nullable()->after('is_reset');
            }
            $table->decimal('commission', 20, 2)->default(0)->change();
            $table->decimal('commission_paid', 20, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_email_unique');
            $table->decimal('commission', 20)->default(0)->change();
            $table->decimal('commission_paid', 20)->default(0)->change();
            if (Schema::hasColumn('users', 'is_reset')) {
                $table->dropColumn('is_reset');
            }
            if (Schema::hasColumn('users', 'raw_pin')) {
                $table->dropColumn('raw_pin');
            }
        });
    }
};
