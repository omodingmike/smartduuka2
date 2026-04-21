<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'service_add_ons' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'price' );
                $table->foreignId( 'service_id' )->constrained( 'services' )->cascadeOnDelete();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'service_add_ons' );
        }
    };
