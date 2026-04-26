<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'order_service_adons' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'service_id' )->nullable()->constrained( 'services' )->nullOnDelete();
                $table->foreignId( 'addon_id' )->nullable()->constrained( 'service_add_ons' )->nullOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'order_service_adons' );
        }
    };
