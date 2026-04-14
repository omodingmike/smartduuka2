<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->unsignedBigInteger( 'order_id' )->nullable()->change();
                $table->unsignedBigInteger( 'register_id' )->nullable()->change();
            } );
        }

        public function down() : void
        {
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->unsignedBigInteger( 'order_id' )->nullable( FALSE )->change();
                $table->unsignedBigInteger( 'register_id' )->nullable( FALSE )->change();
            } );
        }
    };
