<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('activity_logs' , function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('user_type');
                $table->string('action');
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('activity_logs');
        }
    };
