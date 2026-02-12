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
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->foreignId( 'product_attribute_id' )->nullable()->constrained( 'product_attributes' )->onDelete( 'set null' );
                $table->foreignId( 'product_attribute_option_id' )->nullable()->constrained( 'product_attribute_options' )->onDelete( 'set null' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'stocks' , function (Blueprint $table) {
                $table->dropForeign( [ 'product_attribute_id' ] );
                $table->dropForeign( [ 'product_attribute_option_id' ] );
                $table->dropColumn( [ 'product_attribute_id' , 'product_attribute_option_id' ] );
            } );
        }
    };
