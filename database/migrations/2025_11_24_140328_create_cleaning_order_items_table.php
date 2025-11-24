<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'cleaning_order_items' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'cleaning_service_id' )->constrained( 'cleaning_services' );
                $table->string( 'description' );
                $table->unsignedSmallInteger( 'quantity' );
                $table->string( 'notes' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'cleaning_order_items' );
        }
    };
