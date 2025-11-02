<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'commission_payouts' , function (Blueprint $table) {
                $table->id();
                $table->string( 'applies_to' );
                $table->decimal( 'amount' ,20);
                $table->foreignId( 'user_id' )->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId( 'role_id' )->nullable()->constrained('roles')->nullOnDelete();
                $table->dateTime( 'date' );
                $table->string( 'reference' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'commission_payouts' );
        }
    };
