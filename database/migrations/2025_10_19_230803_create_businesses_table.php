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
            Schema::create( 'businesses' , function (Blueprint $table) {
                $table->id();
                $table->string( 'business_id' );
                $table->string( 'project_id' );
                $table->string( 'business_name' );
                $table->string( 'phone_number' );
                $table->string( 'reminder_sent' );
                $table->string( 'expired_sent' );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'businesses' );
        }

        public function shouldRun() : bool
        {
            return config( 'app.main_app' ) == 'true';
        }
    };
