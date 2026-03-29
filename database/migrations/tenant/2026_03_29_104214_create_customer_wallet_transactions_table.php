<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'customer_wallet_transactions' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained( 'users' )->cascadeOnDelete();
                $table->decimal( 'amount' );
                $table->foreignId( 'payment_method_id' )->constrained( 'payment_methods' );
                $table->string( 'reference' );
                $table->unsignedTinyInteger( 'type' );
                $table->decimal( 'balance' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'customer_wallet_transactions' );
        }
    };
