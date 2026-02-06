<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->string( 'driver' )->nullable();
                $table->string( 'number_plate' )->nullable();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->dropColumn( 'driver' );
                $table->dropColumn( 'number_plate' );
            } );
        }
    };
