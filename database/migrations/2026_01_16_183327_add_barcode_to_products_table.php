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
            Schema::table( 'products' , function (Blueprint $table) {
                $table->string( 'barcode' )->unique();
                $table->unsignedSmallInteger( 'returnable' );
                $table->foreignId( 'weight_unit_id' )->references( 'id' )->on( 'units' )->onDelete( 'cascade' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'products' , function (Blueprint $table) {
                $table->dropColumn( 'barcode' );
                $table->dropColumn( 'type' );
                $table->dropColumn( 'returnable' );
                $table->dropColumn( 'weight_unit_id' );
            } );
        }
    };
