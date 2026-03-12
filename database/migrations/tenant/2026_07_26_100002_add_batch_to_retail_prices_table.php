<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            if ( ! Schema::hasColumn( 'retail_prices' , 'batch' ) ) {
                Schema::table( 'retail_prices' , function (Blueprint $table) {
                    $table->string( 'batch' )->nullable();
                } );
                DB::table( 'retail_prices' )->update( [ 'batch' => time() ] );
            }
        }

        public function down() : void
        {
            if ( Schema::hasColumn( 'retail_prices' , 'batch' ) ) {
                Schema::table( 'retail_prices' , function (Blueprint $table) {
                    $table->dropColumn( 'batch' );
                } );
            }
        }
    };
