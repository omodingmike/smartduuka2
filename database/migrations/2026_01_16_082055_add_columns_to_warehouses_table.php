<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'warehouses' , function (Blueprint $table) {
                $table->string( 'manager' )->nullable();
                $table->string( 'capacity' )->nullable();
                $table->unsignedSmallInteger( 'status' )->default( Status::ACTIVE );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'warehouses' , function (Blueprint $table) {
                $table->dropColumn( 'manager' );
                $table->dropColumn( 'capacity' );
                $table->dropColumn( 'status' );
            } );
        }
    };
