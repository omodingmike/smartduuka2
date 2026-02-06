<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to handle case-sensitive column names in PostgreSQL
        DB::statement('ALTER TABLE addresses RENAME COLUMN "addressLine" TO address_line');
        DB::statement('ALTER TABLE addresses RENAME COLUMN "isDefault" TO is_default');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE addresses RENAME COLUMN address_line TO "addressLine"');
        DB::statement('ALTER TABLE addresses RENAME COLUMN is_default TO "isDefault"');
    }
};
