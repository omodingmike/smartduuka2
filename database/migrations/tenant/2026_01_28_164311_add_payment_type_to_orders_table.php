<?php

    use App\Enums\OrderChannel;
    use App\Enums\PaymentType;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->unsignedInteger( 'payment_type' )->default( PaymentType::CASH );
                $table->unsignedInteger( 'channel' )->default( OrderChannel::POS_TERMINAL );
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->dropColumn( 'payment_type' );
                $table->dropColumn( 'channel' );
            } );
        }
    };
