<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('retail_prices' , function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('type');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('unit_id');
                $table->decimal('price' , 10 , 2);
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('retail_prices');
        }
    };
