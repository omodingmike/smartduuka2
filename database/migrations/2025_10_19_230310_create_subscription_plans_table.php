<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        protected $connection ='pgsql2';
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::dropIfExists( 'subscription_plans' );
            Schema::create( 'subscription_plans' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->decimal( 'amount' );
                $table->unsignedInteger( 'duration' );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'subscription_plans' );
        }
        public function shouldRun() : bool
        {
            return config( 'app.main_app' ) == 'true';
        }
    };
