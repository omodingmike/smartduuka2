<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->unsignedBigInteger( 'creator' )->nullable()->change();

                // Add the foreign key constraint
                $table->foreign( 'creator' )
                      ->references( 'id' )
                      ->on( 'users' )
                      ->onDelete( 'set null' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->dropForeign( [ 'creator' ] );
                $table->integer( 'creator' )->nullable()->change();
            } );
        }
    };
