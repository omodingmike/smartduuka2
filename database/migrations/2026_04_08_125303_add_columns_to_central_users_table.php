<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
//                $table->decimal( 'commission' , 20 , 2 )->default( 0 );
//                $table->decimal( 'commission_paid' , 20 , 2 )->default( 0 );
//                $table->string( 'phone2' )->nullable()->unique();
//                $table->string( 'type' )->nullable();
//                $table->string( 'notes' )->nullable();
//                $table->timestamp( 'last_login_date' )->nullable();
//                $table->string( 'department' )->nullable();
//                $table->boolean( 'force_reset' )->default( FALSE );
            if (!Schema::hasColumn('users', 'tenant_id')) {
                $table->string('tenant_id')->nullable();
            }
//                $table->boolean( 'is_reset' )->default( FALSE );
//                $table->string( 'raw_pin' )->nullable();
            if (!Schema::hasColumn('users', 'global_id')) {
                $table->string('global_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [
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
                'global_id',
            ];

            $existingColumns = [];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $existingColumns[] = $column;
                }
            }

            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
