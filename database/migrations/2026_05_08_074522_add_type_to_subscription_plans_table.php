<?php

    use App\Enums\SubscriptionPlanType;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'subscription_plans' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'type' )->default( SubscriptionPlanType::Starter );
                $table->integer( 'setup' )->default( 0 );
            } );
        }

        public function down() : void
        {
            Schema::table( 'subscription_plans' , function (Blueprint $table) {
                $table->dropColumn( [ 'type' , 'setup' ] );
            } );
        }
    };
