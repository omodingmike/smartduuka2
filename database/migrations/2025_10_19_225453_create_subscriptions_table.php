<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        protected $connection = 'pgsql2';

        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::create( 'subscriptions' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'plan_id' );
                $table->string( 'invoice_no' )->nullable();
                $table->string( 'external_id' )->nullable();
                $table->string( 'vendor_transaction_id' )->nullable();
                $table->string( 'vendor_message' )->nullable();
                $table->string( 'phone' )->nullable();
                $table->decimal( 'amount' )->nullable();
                $table->dateTime( 'expires_at' )->nullable();
                $table->dateTime( 'starts_at' )->nullable();
                $table->string( 'project_id' )->nullable();
                $table->string( 'business_id' )->nullable();
                $table->string( 'status' )->default( 'pending' );
                $table->string( 'payment_status' )->default( 'pending' );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'subscriptions' );
        }

        public function shouldRun() : bool
        {
            return config( 'app.main_app' ) == 'true';
        }
    };
