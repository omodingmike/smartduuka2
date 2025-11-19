<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'cleaning_services' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->foreignId( 'cleaning_service_category_id' )->constrained( 'cleaning_service_categories' );
                $table->decimal( 'price' );
                $table->string( 'description' )->nullable();
                $table->integer( 'type' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'cleaning_services' );
        }
    };
