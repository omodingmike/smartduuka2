<?php

    use App\Enums\SubscriptionPaymentStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {

        public function up() : void
        {
            Schema::dropIfExists( 'tenant_subscriptions' );
            Schema::create( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->id();
                $table->string( 'tenant_id' )->nullable();
                $table->foreign( 'tenant_id' )->references( 'id' )->on( 'tenants' )->nullOnDelete();
                $table->foreignId( 'billing_cycle_id' )->references( 'id' )->on( 'billing_cycles' )->cascadeOnDelete();
                $table->foreignId( 'subscription_plan_id' )->constrained()->cascadeOnDelete();
                $table->string( 'phone' );
                $table->string( 'transaction_id' )->nullable();
                $table->string( 'invoice_no' )->nullable();
                $table->decimal( 'amount' );
                $table->unsignedTinyInteger( 'status' );
                $table->unsignedTinyInteger( 'payment_status' )->default( SubscriptionPaymentStatus::Pending );
                $table->dateTime( 'expires_at' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'tenant_subscriptions' );
        }
    };
