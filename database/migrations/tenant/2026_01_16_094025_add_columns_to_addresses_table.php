<?php

    use App\Enums\Ask;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'addresses' , function (Blueprint $table) {
                $table->string( 'full_name' )->nullable()->change();
                $table->string( 'country_code' )->nullable()->change();
                $table->string( 'phone' )->nullable()->change();
                $table->string( 'country' )->nullable()->change();
                $table->string( 'address' )->nullable()->change();
                $table->string( 'type' )->nullable();
                $table->string( 'addressLine' )->nullable();
                $table->string( 'isDefault' )->default( Ask::NO );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'addresses' , function (Blueprint $table) {
                $table->dropColumn( 'type' );
                $table->dropColumn( 'addressLine' );
                $table->dropColumn( 'isDefault' );
            } );
        }
    };
