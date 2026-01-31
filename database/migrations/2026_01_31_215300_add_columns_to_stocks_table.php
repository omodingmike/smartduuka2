<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->decimal( 'quantity_ordered' )->default( 0 );
                $table->decimal( 'quantity_received' )->default( 0 );
            } );
        }

        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->dropColumn( 'quantity_ordered' );
                $table->dropColumn( 'quantity_received' );
            } );
        }
    };
