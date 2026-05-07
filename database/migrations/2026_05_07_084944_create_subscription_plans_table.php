<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'subscription_plans' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'description' );
                $table->json( 'features' );
                $table->unsignedInteger( 'base_amount' );
                $table->boolean( 'popular' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'subscription_plans' );
        }
    };
