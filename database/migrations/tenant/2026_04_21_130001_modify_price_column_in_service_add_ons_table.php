<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE service_add_ons ALTER COLUMN price TYPE DECIMAL(20,2) USING price::numeric(20,2)');
    }
};
