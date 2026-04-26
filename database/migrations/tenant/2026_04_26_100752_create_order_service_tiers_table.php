<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'order_service_tiers' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'service_id' )->nullable()->constrained( 'services' )->nullOnDelete();
                $table->foreignId( 'service_tier_id' )->nullable()->constrained( 'service_tiers' )->nullOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'order_service_tiers' );
        }
    };
