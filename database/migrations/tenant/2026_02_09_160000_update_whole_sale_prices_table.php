<?php

    use App\Models\Product;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'whole_sale_prices' , function (Blueprint $table) {
                $table->dropForeign( [ 'product_id' ] );
                $table->renameColumn( 'product_id' , 'item_id' );
                $table->string( 'item_type' )->after( 'item_id' )->default( Product::class );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'whole_sale_prices' , function (Blueprint $table) {
                $table->dropColumn( 'item_type' );
                $table->renameColumn( 'item_id' , 'product_id' );
                $table->foreign( 'product_id' )->references( 'id' )->on( 'products' );
            } );
        }
    };
