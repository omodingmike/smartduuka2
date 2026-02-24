<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'expense_payments' , function (Blueprint $table) {
                $table->renameColumn( 'paymentMethod' , 'payment_method_id' );
            } );

            Schema::table( 'expense_payments' , function (Blueprint $table) {
                $table->foreign( 'payment_method_id' )->references( 'id' )->on( 'payment_methods' )->onDelete( 'cascade' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'expense_payments' , function (Blueprint $table) {
                $table->dropForeign( [ 'payment_method_id' ] );
                $table->renameColumn( 'payment_method_id' , 'paymentMethod' );
            } );
        }
    };
