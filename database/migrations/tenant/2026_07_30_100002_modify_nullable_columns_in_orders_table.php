<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->decimal( 'tax' , 16 )->nullable()->default( 0 )->change();
                $table->decimal( 'discount' , 16 )->nullable()->default( 0 )->change();
                $table->decimal( 'shipping_charge' , 16 )->nullable()->change();
                $table->string( 'channel' )->nullable()->change();
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->decimal( 'tax' , 16 )->nullable( FALSE )->change();
                $table->decimal( 'discount' , 16 )->nullable( FALSE )->change();
                $table->decimal( 'shipping_charge' , 16 )->nullable( FALSE )->change();
                $table->string( 'channel' )->nullable( FALSE )->change();
            } );
        }
    };
