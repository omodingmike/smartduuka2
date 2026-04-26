<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            // orders: user_id + status (used by the base orders() relationship scope)
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->index( [ 'user_id' , 'status' ] , 'idx_orders_user_status' );
            } );

            // orders: user_id + payment_type + status (used by creditAndDeposit / creditOrdersQuery)
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->index( [ 'user_id' , 'payment_type' , 'status' ] , 'idx_orders_user_payment_status' );
            } );

            // orders: order_datetime (used by orderBy / MIN in oldestCreditOrder)
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->index( 'order_datetime' , 'idx_orders_order_datetime' );
            } );

            // pos_payments: order_id (used by the correlated subquery in credits / creditOrdersQuery)
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->index( 'order_id' , 'idx_pos_payments_order_id' );
            } );

            // customer_wallet_transactions: user_id (used by walletTransactions())
            Schema::table( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->index( 'user_id' , 'idx_wallet_transactions_user_id' );
            } );

            // legacy_debts: user_id (used by legacyDebts()->sum())
            Schema::table( 'legacy_debts' , function (Blueprint $table) {
                $table->index( 'user_id' , 'idx_legacy_debts_user_id' );
            } );

            Schema::table( 'customer_payments' , function (Blueprint $table) {
                $table->index( [ 'user_id' , 'customer_payment_type' ] , 'idx_customer_payments_user_type' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->dropIndex( 'idx_orders_user_status' );
                $table->dropIndex( 'idx_orders_user_payment_status' );
                $table->dropIndex( 'idx_orders_order_datetime' );
            } );

            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->dropIndex( 'idx_pos_payments_order_id' );
            } );

            Schema::table( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->dropIndex( 'idx_wallet_transactions_user_id' );
            } );

            Schema::table( 'legacy_debts' , function (Blueprint $table) {
                $table->dropIndex( 'idx_legacy_debts_user_id' );
            } );

            Schema::table( 'customer_payments' , function (Blueprint $table) {
                $table->dropIndex( 'idx_customer_payments_user_type' );
            } );
        }
    };