<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'purchase_payments' , function (Blueprint $table) {
                $table->dropForeign( [ 'purchase_id' ] );
                $table->foreign( 'purchase_id' )
                      ->references( 'id' )
                      ->on( 'purchases' )
                      ->onDelete( 'cascade' );

                $table->foreignId( 'register_id' )
                      ->after( 'purchase_id' )
                      ->nullable()
                      ->constrained()
                      ->cascadeOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::table( 'purchase_payments' , function (Blueprint $table) {
                $table->dropForeign( [ 'register_id' ] );
                $table->dropColumn( 'register_id' );

                $table->dropForeign( [ 'purchase_id' ] );
                $table->foreign( 'purchase_id' )
                      ->references( 'id' )
                      ->on( 'purchases' );
            } );
        }
    };
