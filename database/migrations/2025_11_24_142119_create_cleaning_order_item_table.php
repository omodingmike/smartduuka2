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
            Schema::create( 'cleaning_order_item' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'cleaning_order_id' )->constrained( 'cleaning_orders' );
                $table->foreignId( 'cleaning_order_item_id' )->constrained( 'cleaning_order_items' );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'cleaning_order_item' );
        }
    };
