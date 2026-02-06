<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'stock_products' , function (Blueprint $table) {
                $table->id();
                $table->string( 'item_type' );
                $table->unsignedBigInteger( 'item_id' );
                $table->foreignId( 'stock_id' )->constrained( 'stocks' )->cascadeOnDelete();
                $table->foreignId( 'unit_id' )->constrained( 'units' )->cascadeOnDelete();
                $table->decimal( 'quantity' , 20 );
                $table->decimal( 'subtotal' , 20 );
                $table->decimal( 'total' , 20 );
                $table->dateTime( 'expiry_date' )->nullable();
                $table->decimal( 'weight' )->nullable();
                $table->string( 'serial' )->nullable();
                $table->string( 'notes' )->nullable();
                $table->unsignedSmallInteger( 'difference' )->nullable();
                $table->unsignedSmallInteger( 'discrepancy' )->nullable();
                $table->unsignedSmallInteger( 'classification' )->nullable();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'stock_products' );
        }
    };
