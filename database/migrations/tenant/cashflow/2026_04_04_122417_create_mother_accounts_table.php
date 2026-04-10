<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'mother_accounts' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' )->unique();
                $table->decimal( 'cash_in' , 20 )->default( 0 );
                $table->decimal( 'cash_out' , 20 )->default( 0 );
                $table->unsignedTinyInteger( 'type' )->nullable();
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'mother_accounts' );
        }
    };
