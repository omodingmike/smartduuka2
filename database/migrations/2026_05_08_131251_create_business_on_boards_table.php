<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'business_on_boards' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'tenant' );
                $table->string( 'email' );
                $table->string( 'phone' );
                $table->string( 'mobile_phone_number' );
                $table->string( 'address' );
                $table->string( 'admin_email' );
                $table->string( 'admin_password' );
                $table->string( 'admin_pin' );
                $table->string( 'payment_method' );
                $table->integer( 'plan_id' );
                $table->integer( 'cycle_id' );
                $table->unsignedInteger( 'amount' );
                $table->string( 'admin_name' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'business_on_boards' );
        }
    };
