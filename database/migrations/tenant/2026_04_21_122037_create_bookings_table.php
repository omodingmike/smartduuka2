<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'bookings' , function (Blueprint $table) {
                $table->id();
                $table->string( 'customer_name' );
                $table->string( 'customer_phone' );
                $table->foreignId( 'service_id' )->constrained( 'services' )->cascadeOnDelete();
                $table->dateTime( 'date' );
                $table->unsignedTinyInteger( 'status' );
                $table->decimal( 'total' , 20 );
                $table->text( 'notes' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'bookings' );
        }
    };
