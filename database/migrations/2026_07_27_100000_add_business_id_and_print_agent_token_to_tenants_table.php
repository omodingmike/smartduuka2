<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'tenants' , function (Blueprint $table) {
                $table->string( 'business_id' )->unique()->nullable();
                $table->string( 'print_agent_token' )->unique()->nullable();
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenants' , function (Blueprint $table) {
                $table->dropColumn( 'business_id' );
                $table->dropColumn( 'print_agent_token' );
            } );
        }
    };
