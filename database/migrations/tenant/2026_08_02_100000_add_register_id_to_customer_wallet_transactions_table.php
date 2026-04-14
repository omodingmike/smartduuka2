<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->foreignId( 'register_id' )->nullable()->after( 'payment_method_id' )->constrained( 'registers' )->nullOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::table( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->dropForeign( [ 'register_id' ] );
                $table->dropColumn( 'register_id' );
            } );
        }
    };
