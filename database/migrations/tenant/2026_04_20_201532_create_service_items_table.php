<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'service_items' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'service_id' )->constrained()->cascadeOnDelete();
                $table->morphs( 'item' );
                $table->decimal( 'quantity' );
                $table->morphs( 'price' );
                $table->decimal( 'total' , 20 );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'service_items' );
        }
    };
