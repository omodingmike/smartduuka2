<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('ledger_transactions' , function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ledger_id');
                $table->string('narration');
                $table->decimal('credit' , 20);
                $table->decimal('debit' , 20);
                $table->decimal('balance' , 20)->default(0);
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('ledger_transactions');
        }
    };
