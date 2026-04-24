<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->decimal( 'offer_amount' , 20 )->nullable();
                $table->text( 'offer_message' )->nullable();
                $table->text( 'decline_message' )->nullable();
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->dropColumn( 'offer_amount' );
                $table->dropColumn( 'offer_message' );
                $table->dropColumn( 'decline_message' );
            } );
        }
    };
