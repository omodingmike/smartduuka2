<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'payment_method_transactions' , function (Blueprint $table) {
                $table->dropForeign( [ 'order_id' ] );
                $table->dropColumn( 'order_id' );
                $table->nullableMorphs( 'item' );
            } );
        }

        public function down() : void
        {
            Schema::table( 'payment_method_transactions' , function (Blueprint $table) {
                $table->dropMorphs( 'item' );
                $table->foreignId( 'order_id' )->constrained( 'orders' )->cascadeOnDelete();
            } );
        }
    };
