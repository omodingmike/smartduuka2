<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'booking_activity_logs' , function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger( 'status' );
                $table->string( 'note' );
                $table->foreignId( 'user_id' )->constrained( 'users' );
                $table->foreignId( 'booking_id' )->constrained( 'bookings' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'booking_activity_logs' );
        }
    };
