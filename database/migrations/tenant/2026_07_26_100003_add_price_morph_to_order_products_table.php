<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                if ( ! Schema::hasColumn( 'order_products' , 'price_type' ) && ! Schema::hasColumn( 'order_products' , 'price_id' ) ) {
                    $table->nullableMorphs( 'price' );
                }
            } );
        }

        public function down() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                if ( Schema::hasColumn( 'order_products' , 'price_type' ) && Schema::hasColumn( 'order_products' , 'price_id' ) ) {
                    $table->dropMorphs( 'price' );
                }
            } );
        }
    };
