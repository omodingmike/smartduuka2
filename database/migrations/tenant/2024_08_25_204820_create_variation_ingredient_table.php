<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('variation_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('variation_id');
            $table->unsignedInteger('ingredient_id');
            $table->decimal('quantity')->default(0);
            $table->bigInteger('buying_price')->default(0);
            $table->bigInteger('total')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('variation_ingredient');
    }
};
