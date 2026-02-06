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
            Schema::table( 'retail_prices' , function (Blueprint $table) {
                $table->renameColumn( 'type' , 'item_type' );
                $table->string('item_type')->change();
                $table->renameColumn( 'product_id' , 'item_id' );
                $table->renameColumn( 'price' , 'buying_price' );
                $table->string( 'selling_price' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'retail_prices' , function (Blueprint $table) {
                $table->dropColumn( 'selling_price' );
                $table->renameColumn( 'item_type' , 'type' );
                $table->renameColumn( 'item_id' , 'product_id' );
                $table->renameColumn( 'buying_price' , 'price' );
            } );
        }
    };
