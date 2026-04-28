<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'quotation_item_type' )->nullable();
            } );
        }

        public function down() : void
        {
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->dropColumn( 'quotation_item_type' );
            } );
        }
    };
