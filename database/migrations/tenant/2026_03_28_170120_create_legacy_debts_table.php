<?php

    use App\Enums\PaymentStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'legacy_debts' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained( 'users' )->nullOnDelete();
                $table->decimal( 'amount' );
                $table->dateTime( 'date' );
                $table->string( 'notes' );
                $table->unsignedTinyInteger( 'payment_status' )->default( PaymentStatus::UNPAID );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'legacy_debts' );
        }
    };
