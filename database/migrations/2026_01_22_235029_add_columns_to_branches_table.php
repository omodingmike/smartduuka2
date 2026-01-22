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
            Schema::table( 'branches' , function (Blueprint $table) {
                $table->string( 'city' )->nullable()->change();
                $table->string( 'state' )->nullable()->change();
                $table->string( 'zip_code' )->nullable()->change();
                $table->string( 'manager' );
                $table->string( 'code' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'branches' , function (Blueprint $table) {
                $table->dropColumn( 'manager' );
                $table->dropColumn( 'code' );
            } );
        }
    };
