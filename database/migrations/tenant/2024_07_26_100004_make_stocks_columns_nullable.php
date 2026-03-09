<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->bigInteger( 'product_id' )->nullable()->change();
                $table->string( 'model_type' )->nullable()->change();
                $table->unsignedBigInteger( 'model_id' )->nullable()->change();
                $table->decimal( 'subtotal' , 20 , 2 )->nullable()->change();
                $table->decimal( 'tax' , 20 , 2 )->nullable()->change();
            } );
        }

        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->bigInteger( 'product_id' )->nullable( FALSE )->change();
                $table->string( 'model_type' )->nullable( FALSE )->change();
                $table->unsignedBigInteger( 'model_id' )->nullable( FALSE )->change();
                $table->decimal( 'subtotal' , 20 , 2 )->nullable( FALSE )->change();
                $table->decimal( 'tax' , 20 , 2 )->nullable( FALSE )->change();
            } );
        }
    };
