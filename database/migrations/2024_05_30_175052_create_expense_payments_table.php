<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema ::create('expense_payments', function (Blueprint $table) {
                $table -> id();
                $table -> integer('user_id');
                $table -> integer('expense_id');
                $table -> dateTime('date');
                $table -> string('referenceNo') -> nullable();
                $table -> integer('amount');
                $table -> integer('paymentMethod');
                $table -> string('attachment') -> nullable();
                $table -> timestamps();
            });
        }

        public function down() : void
        {
            Schema ::dropIfExists('expense_payments');
        }
    };
