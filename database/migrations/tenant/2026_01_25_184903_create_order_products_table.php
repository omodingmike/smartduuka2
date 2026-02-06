<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'order_products' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'order_id' )->constrained()->cascadeOnDelete();
                $table->string( 'item_type' );
                $table->unsignedBigInteger( 'item_id' );
                $table->unsignedInteger( 'quantity' );
                $table->decimal( 'total' );
                $table->decimal( 'unit_price' );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'order_products' );
        }
    };
