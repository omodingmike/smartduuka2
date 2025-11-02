<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('production_processes' , function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('setup_id');
                $table->unsignedInteger('quantity');
                $table->unsignedInteger('status');
                $table->unsignedInteger('actual_quantity')->nullable();
                $table->string('damage_type')->nullable();
                $table->string('damage_reason')->nullable();
                $table->unsignedInteger('damage_result')->nullable();
                $table->datetime('start_date')->default(now());
                $table->datetime('schedule_start_date')->nullable();
                $table->datetime('end_date')->nullable();
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('production_processes');
        }
    };
