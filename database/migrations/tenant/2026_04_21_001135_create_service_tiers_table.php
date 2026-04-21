<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'service_tiers' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->decimal( 'price' );
                $table->text( 'features' );
                $table->foreignId( 'service_id' )->constrained( 'services' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'service_tiers' );
        }
    };
