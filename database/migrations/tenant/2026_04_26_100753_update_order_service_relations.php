<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'order_service_adons' , function (Blueprint $table) {
                $table->dropForeign( [ 'service_id' ] );
                $table->dropColumn( 'service_id' );
                $table->foreignId( 'order_service_product_id' )->constrained( 'order_services' )->cascadeOnDelete();
            } );

            Schema::table( 'order_service_tiers' , function (Blueprint $table) {
                $table->dropForeign( [ 'service_id' ] );
                $table->dropColumn( 'service_id' );
                $table->foreignId( 'order_service_product_id' )->constrained( 'order_services' )->cascadeOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::table( 'order_service_adons' , function (Blueprint $table) {
                $table->dropForeign( [ 'order_service_product_id' ] );
                $table->dropColumn( 'order_service_product_id' );
                $table->foreignId( 'service_id' )->constrained( 'services' );
            } );

            Schema::table( 'order_service_tiers' , function (Blueprint $table) {
                $table->dropForeign( [ 'order_service_product_id' ] );
                $table->dropColumn( 'order_service_product_id' );
                $table->foreignId( 'service_id' )->constrained( 'services' );
            } );
        }
    };
