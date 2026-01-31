<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'expenses' , function (Blueprint $table) {
                $table->renameColumn( 'category' , 'expense_category_id' );
                $table->renameColumn( 'paymentMethod' , 'payment_method_id' );
                $table->renameColumn( 'referenceNo' , 'reference_no' );
                $table->renameColumn( 'isRecurring' , 'is_recurring' );
                $table->dropColumn( 'user_id' );
            } );
            Schema::table( 'expense_categories' , function (Blueprint $table) {
                $table->dropColumn( 'user_id' );
            } );

            Schema::table( 'expenses' , function (Blueprint $table) {
                $table->unsignedBigInteger( 'expense_category_id' )->nullable()->change();
                $table->unsignedBigInteger( 'payment_method_id' )->change();
                $table->foreign( 'expense_category_id' )
                      ->references( 'id' )
                      ->on( 'expense_categories' )
                      ->onDelete( 'cascade' );

                $table->foreign( 'payment_method_id' )
                      ->references( 'id' )
                      ->on( 'payment_methods' )
                      ->onDelete( 'cascade' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'expenses' , function (Blueprint $table) {
                $table->dropForeign( [ 'expense_category_id' ] );
                $table->dropForeign( [ 'payment_method_id' ] );
                $table->integer( 'user_id' );
            } );
            Schema::table( 'expense_categories' , function (Blueprint $table) {
                $table->integer( 'user_id' );
            } );

            Schema::table( 'expenses' , function (Blueprint $table) {
                $table->renameColumn( 'expense_category_id' , 'category' );
                $table->renameColumn( 'payment_method_id' , 'paymentMethod' );
                $table->renameColumn( 'reference_no' , 'referenceNo' );
                $table->renameColumn( 'is_recurring' , 'isRecurring' );
            } );
        }
    };