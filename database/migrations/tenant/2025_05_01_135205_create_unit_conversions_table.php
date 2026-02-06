<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('base_unit_id')->comment('Base unit ID');
            $table->unsignedInteger('other_unit_id')->comment('Other unit ID');
            $table->decimal('conversion_rate' , 10 , 4)->default(1)->comment('Conversion rate');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
