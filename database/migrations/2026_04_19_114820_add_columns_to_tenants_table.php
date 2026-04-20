<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'tenants' , function (Blueprint $table) {
                $table->string( 'name' )->nullable();
                $table->string( 'type' )->nullable();
                $table->string( 'location' )->nullable();
                $table->string( 'email' )->nullable();
                $table->string( 'phone' )->nullable();
                $table->string( 'domain' )->nullable();
                $table->string( 'status' )->nullable();
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenants' , function (Blueprint $table) {
                $table->dropColumn( 'name' );
                $table->dropColumn( 'type' );
                $table->dropColumn( 'location' );
                $table->dropColumn( 'email' );
                $table->dropColumn( 'phone' );
                $table->dropColumn( 'domain' );
                $table->dropColumn( 'status' );
            } );
        }
    };
