<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('chart_of_account_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->boolean('can_delete')->default(false);
                $table->enum('type', ['debit', 'credit']);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('chart_of_account_groups')
                      ->onDelete('restrict');
            });

        }

        public function down() : void
        {
            Schema::dropIfExists('chart_of_account_groups');
        }
    };
