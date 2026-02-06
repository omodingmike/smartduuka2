<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'whole_sale_prices' , function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger( 'minQuantity' );
                $table->decimal( 'price' );
                $table->foreignId( 'product_id' )->constrained( 'products' );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'whole_sale_prices' );
        }
    };
