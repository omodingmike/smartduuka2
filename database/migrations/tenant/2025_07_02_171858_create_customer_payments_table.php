<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('customer_payments' , function (Blueprint $table) {
                $table->id();
                $table->dateTime('date');
                $table->decimal('amount' , 18);
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('payment_method_id');
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('customer_payments');
        }
    };
