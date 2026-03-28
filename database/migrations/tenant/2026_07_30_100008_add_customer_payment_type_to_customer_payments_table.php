<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'customer_payments' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'customer_payment_type' )->nullable()->after( 'payment_method_id' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'customer_payments' , function (Blueprint $table) {
                $table->dropColumn( 'customer_payment_type' );
            } );
        }
    };
