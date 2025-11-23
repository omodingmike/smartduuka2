<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'cleaning_service_taxes' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'tax_id' )->constrained();
                $table->foreignId( 'cleaning_service_id' )->constrained();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'cleaning_service_taxes' );
        }
    };
