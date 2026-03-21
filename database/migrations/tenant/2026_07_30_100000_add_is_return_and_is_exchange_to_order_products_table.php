<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->boolean( 'is_return' )->default( FALSE );
                $table->boolean( 'is_exchange' )->default( FALSE );
            } );
        }

        public function down() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->dropColumn( [ 'is_return' , 'is_exchange' ] );
            } );
        }
    };
