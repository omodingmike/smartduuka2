<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'expenses' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->integer( 'amount' );
                $table->dateTime( 'date' );
                $table->integer( 'category' )->nullable();
                $table->integer( 'user_id' );
                $table->string( 'note' )->nullable();
                $table->integer( 'paymentMethod' );
                $table->string( 'referenceNo' )->nullable();
                $table->string( 'attachment' )->nullable();
                $table->string( 'recurs' )->nullable();
                $table->boolean( 'isRecurring' )->default( FALSE );
                $table->integer( 'repetitions' )->default( 0 )->after( 'recurs' );
                $table->integer( 'paid' )->default( 0 )->after( 'repetitions' );
                $table->dateTime( 'paid_on' )->nullable()->default( NULL )->after( 'repetitions' );
                $table->dateTime( 'repeats_on' )->nullable()->default( NULL )->after( 'repetitions' );
                $table->integer( 'count' )->default( 0 );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'expenses' );
        }
    };
