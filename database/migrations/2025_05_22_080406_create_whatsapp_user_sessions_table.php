<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            if ( config('app.main_app') ) {
                Schema::create('whatsapp_user_sessions' , function (Blueprint $table) {
                    $table->id();
                    $table->string('phone_number')->unique();
                    $table->string('state')->default('welcome');
                    $table->json('data')->nullable();
                    $table->timestamps();
                });
            }
        }


        public function down() : void
        {
            if ( config('app.main_app') ) {
                Schema::dropIfExists('whatsapp_user_sessions');
            }
        }
    };
