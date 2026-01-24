<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->unsignedInteger( 'approve_quantity' )->default( 0 );
                $table->unsignedInteger( 'request_quantity' )->default( 0 );
            } );
        }

        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->dropColumn( 'approve_quantity' );
                $table->dropColumn( 'request_quantity' );
            } );
        }
    };
