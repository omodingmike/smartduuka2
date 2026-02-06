<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create( 'payment_gateways' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'slug' );
                $table->longText( 'misc' )->nullable();
                $table->tinyInteger( 'status' )->default( Status::ACTIVE );
                $table->string( 'creator_type' )->nullable();
                $table->bigInteger( 'creator_id' )->nullable();
                $table->string( 'editor_type' )->nullable();
                $table->bigInteger( 'editor_id' )->nullable();
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists( 'payment_gateways' );
        }
    };