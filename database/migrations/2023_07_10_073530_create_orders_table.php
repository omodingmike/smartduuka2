<?php

    use App\Enums\Ask;
    use App\Enums\OrderType;
    use App\Enums\PaymentGateway;
    use App\Enums\PaymentStatus;
    use App\Models\User;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::create( 'orders' , function (Blueprint $table) {
                $table->id();
                $table->string( 'order_serial_no' )->nullable();
                $table->foreignId( 'user_id' )->constrained( 'users' );
                $table->decimal( 'subtotal' , 20  );
                $table->decimal( 'tax' , 13 , 6 )->nullable()->default( 0 );
                $table->decimal( 'discount' , 13 , 6 )->nullable()->default( 0 );
                $table->decimal( 'shipping_charge' , 13 , 6 )->nullable()->default( 0 );
                $table->decimal( 'total' , 20 );
                $table->tinyInteger( 'order_type' )->default( OrderType::DELIVERY );
                $table->dateTime( 'order_datetime' )->default( now() );
                $table->bigInteger( 'payment_method' )->default( PaymentGateway::CASH_ON_DELIVERY );
                $table->tinyInteger( 'payment_status' )->default( PaymentStatus::UNPAID );
                $table->tinyInteger( 'status' );
                $table->tinyInteger( 'active' )->default( ASK::NO );
                $table->text( 'reason' )->nullable();
                $table->string( 'user_type' )->nullable()->after( 'user_id' )->default( User::class );
                $table->tinyInteger('pos_payment_method')->after('payment_status')->nullable();
                $table->string('pos_payment_note', 200)->after('pos_payment_method')->nullable();
                $table->string( 'source' )->nullable();
                $table->integer( 'paid' )->nullable();
                $table->integer( 'change' )->nullable();
                $table->string( 'creator_type' )->nullable();
                $table->bigInteger( 'creator_id' )->nullable();
                $table->string( 'editor_type' )->nullable();
                $table->bigInteger( 'editor_id' )->nullable();
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'orders' );
        }
    };
