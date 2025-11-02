<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('item_raw_materials' , function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('ingredient_id');
                $table->decimal('quantity' , 10 , 2)->default(0);
                $table->bigInteger('buying_price')->default(0);
                $table->bigInteger('total')->default(0);
                $table->unsignedBigInteger('setup_id')->nullable();
                $table->foreign('setup_id')->references('id')->on('production_setups')->onDelete('cascade');
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('item_raw_materials');
        }
    };
