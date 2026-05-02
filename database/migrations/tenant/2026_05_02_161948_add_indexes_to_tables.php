<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        private string $indexName = 'orders_status_user_id_payment_type_index';

        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->index(
                    [
                        'status' , 'user_id' , 'payment_type' , 'order_serial_no' , 'order_datetime' , 'order_type'
                    ] , $this->indexName );
            } );
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->index( 'order_id' );
            } );
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->index( [ 'order_id' ] );
            } );
            Schema::table( 'order_services' , function (Blueprint $table) {
                $table->index( [ 'order_id' , 'service_id' ] );
            } );
            Schema::table( 'users' , function (Blueprint $table) {
                $table->index( [ 'name' ] );
            } );
        }


        public function down() : void
        {
            DB::statement( "DROP INDEX IF EXISTS \"{$this->indexName}\"" );

            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->dropIndex( [ 'order_id' ] );
            } );
            Schema::table( 'order_products' , function (Blueprint $table) {
                $table->dropIndex( [ 'order_id' ] );
            } );
            Schema::table( 'order_services' , function (Blueprint $table) {
                $table->dropIndex( [ 'order_id' , 'service_id' ] );
            } );
            Schema::table( 'users' , function (Blueprint $table) {
                $table->dropIndex( [ 'name' ] );
            } );
        }
    };