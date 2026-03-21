<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'refund_status' )->nullable();
                $table->unsignedTinyInteger( 'return_status' )->nullable();
                $table->unsignedTinyInteger( 'return_type' )->nullable();
                $table->foreignId( 'original_order_id' )->nullable()->references( 'id' )->on( 'orders' )->nullOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->dropForeign( [ 'original_order_id' ] );
                $table->dropColumn( [
                    'refund_status' ,
                    'return_status' ,
                    'return_type' ,
                    'original_order_id'
                ] );
            } );
        }
    };
