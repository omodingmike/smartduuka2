<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('payment_accounts' , function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('amount' , 20)->default(0);
                $table->unsignedInteger('currency_id');
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('payment_accounts');
        }
    };
