<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'whole_sale_prices' , function (Blueprint $table) {
                $table->string( 'batch' )->nullable();
            } );
            DB::table( 'whole_sale_prices' )->update( [ 'batch' => time() ] );
        }

        public function down() : void
        {
            Schema::table( 'whole_sale_prices' , function (Blueprint $table) {
                $table->dropColumn( 'batch' );
            } );
        }
    };
