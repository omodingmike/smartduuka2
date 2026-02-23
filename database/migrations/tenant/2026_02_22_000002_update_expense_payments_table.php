<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'expense_payments' , function (Blueprint $table) {
                $table->integer( 'user_id' )->nullable()->change();
                $table->string( 'referenceNo' )->nullable()->change();
                $table->string( 'attachment' )->nullable()->change();
                $table->foreignId( 'register_id' )->nullable()->constrained( 'registers' )->nullOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::table( 'expense_payments' , function (Blueprint $table) {
                $table->integer( 'user_id' )->nullable( FALSE )->change();
                $table->string( 'referenceNo' )->nullable( FALSE )->change();
                $table->string( 'attachment' )->nullable( FALSE )->change();
                $table->dropForeign( [ 'register_id' ] );
                $table->dropColumn( 'register_id' );
            } );
        }
    };
