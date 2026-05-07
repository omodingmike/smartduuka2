<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\DB;

    return new class extends Migration
    {
        public function up(): void
        {
            // Check if the unique constraint already exists in PostgreSQL
            $exists = DB::select("
            SELECT 1 FROM pg_constraint 
            WHERE conname = 'users_global_id_unique'
        ");

            if (empty($exists)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('global_id', 'users_global_id_unique');
                });
            }
        }

        public function down(): void
        {
            $exists = DB::select("
            SELECT 1 FROM pg_constraint 
            WHERE conname = 'users_global_id_unique'
        ");

            if (!empty($exists)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropUnique('users_global_id_unique');
                });
            }
        }
    };