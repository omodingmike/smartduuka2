<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('pos_payments' , function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('reference_no')->nullable();
                $table->unsignedInteger('amount')->nullable();
                $table->unsignedInteger('order_id')->nullable();
                $table->string('payment_method')->nullable();
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('pos_payments');
        }
    };
