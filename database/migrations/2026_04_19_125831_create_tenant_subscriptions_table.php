<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->id();
                $table->string( 'tenant_id' )->nullable();
                $table->foreign( 'tenant_id' )->references( 'id' )->on( 'tenants' )->nullOnDelete();
                $table->unsignedTinyInteger( 'duration' );
                $table->unsignedTinyInteger( 'plan' );
                $table->decimal( 'setup' );
                $table->unsignedTinyInteger( 'status' );
                $table->decimal( 'amount' );
                $table->dateTime( 'expires_at' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'tenant_subscriptions' );
        }
    };
