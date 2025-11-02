<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('ledgers' , function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->unsignedBigInteger('parent_id');
                $table->unsignedInteger('currency_id');
                $table->decimal('amount' , 20)->default(0);
                $table->enum('type' , [ 'debit' , 'credit' ]);
                $table->foreign('parent_id')
                      ->references('id')
                      ->on('chart_of_account_groups')
                      ->onDelete('restrict');
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('ledgers');
        }
    };
