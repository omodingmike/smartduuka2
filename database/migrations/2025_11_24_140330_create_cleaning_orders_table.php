<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'cleaning_orders' , function (Blueprint $table) {
                $table->id();
                $table->string( 'order_id' );
                $table->foreignId( 'cleaning_service_customer_id' )->constrained( 'cleaning_service_customers' );
                $table->decimal( 'total' );
                $table->dateTime( 'date' );
                $table->unsignedSmallInteger( 'status' );
                $table->unsignedSmallInteger( 'service_method' );
                $table->decimal( 'subtotal' );
                $table->decimal( 'tax' );
                $table->decimal( 'discount' );
                $table->foreignId( 'payment_method_id' )->constrained( 'payment_methods' );
                $table->decimal( 'paid' );
                $table->decimal( 'balance' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'cleaning_orders' );
        }
    };
