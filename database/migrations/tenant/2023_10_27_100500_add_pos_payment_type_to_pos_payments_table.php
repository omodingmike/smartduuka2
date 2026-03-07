<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up()
        {
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'pos_payment_type' )->nullable()->after( 'payment_method_id' );
            } );
        }

        public function down()
        {
            Schema::table( 'pos_payments' , function (Blueprint $table) {
                $table->dropColumn( 'pos_payment_type' );
            } );
        }
    };
