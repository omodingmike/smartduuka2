<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'entities' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' )->unique();
                $table->decimal( 'cleared' , 20 )->default( 0 );
                $table->decimal( 'outstanding' , 20 )->default( 0 );
                $table->unsignedTinyInteger( 'type' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'entities' );
        }
    };
