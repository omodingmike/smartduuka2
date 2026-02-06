<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'registers' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained( 'users' )->cascadeOnDelete();
                $table->decimal( 'opening_float' , 20 );
                $table->string( 'notes' )->nullable();
                $table->unsignedTinyInteger( 'status' );
                $table->decimal( 'expected_float' , 20 )->nullable();
                $table->decimal( 'closing_float' , 20 )->nullable();
                $table->decimal( 'difference' , 20 , 2 )->nullable();
                $table->dateTime( 'closed_at' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'registers' );
        }
    };
