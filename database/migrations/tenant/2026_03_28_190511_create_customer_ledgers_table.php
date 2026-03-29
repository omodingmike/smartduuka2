<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'customer_ledgers' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained()->cascadeOnDelete();
                $table->dateTime( 'date' );
                $table->string( 'reference' );
                $table->string( 'description' );
                $table->decimal( 'bill_amount' , 20 );
                $table->decimal( 'paid' , 20 );
                $table->decimal( 'balance' , 20 );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'customer_ledgers' );
        }
    };
