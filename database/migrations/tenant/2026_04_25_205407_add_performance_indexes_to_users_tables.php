<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                // Covers activeOrders(), creditOrdersQuery(), status filter + datetime sort
                $table->index( [ 'user_id' , 'status' , 'payment_type' , 'order_datetime' ] , 'orders_user_perf_idx' );
            } );

            Schema::table( 'pos_payments' , function (Blueprint $table) {
                // Covers the correlated subquery join
                $table->index( [ 'order_id' , 'amount' ] , 'pos_payments_order_perf_idx' );
            } );

            Schema::table( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->index( [ 'user_id' ] , 'wallet_txn_user_idx' );
            } );

            Schema::table( 'legacy_debts' , function (Blueprint $table) {
                $table->index( [ 'user_id' ] , 'legacy_debts_user_idx' );
            } );
        }

    };
