<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'order_payment_methods' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'payment_method_id' )->constrained( 'payment_methods' )->cascadeOnDelete();
                $table->foreignId( 'order_id' )->constrained( 'orders' )->cascadeOnDelete();
                $table->decimal( 'amount' , 20 );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'order_payment_methods' );
        }
    };
