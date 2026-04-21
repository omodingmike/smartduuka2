<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'services' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->foreignId( 'service_category_id' )->constrained( 'service_categories' )->cascadeOnDelete();
                $table->decimal( 'base_price' , 20 );
                $table->string( 'duration' )->nullable();
                $table->string( 'description' );
                $table->unsignedTinyInteger( 'type' );
                $table->unsignedTinyInteger( 'status' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'services' );
        }
    };
