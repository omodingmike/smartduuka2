<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {

        public function up() : void
        {
            if ( config('app.main_app') ) {
                Schema::create('businesses' , function (Blueprint $table) {
                    $table->id();
                    $table->string('business_id');
                    $table->string('project_id');
                    $table->string('business_name');
                    $table->string('phone_number')->nullable();
                    $table->boolean('reminder_sent')->default(false);
                    $table->boolean('expired_sent')->default(false);
                    $table->timestamps();
                });
            }
        }


        public function down() : void
        {
            if ( config('app.main_app') ) {
                Schema::dropIfExists('businesses');
            }
        }
    };
