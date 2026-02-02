<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'printers' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'connection_type' );
                $table->string( 'profile' );
                $table->string( 'chars' )->nullable();
                $table->string( 'ip' )->nullable();
                $table->string( 'port' )->nullable();
                $table->string( 'path' )->nullable();
                $table->string( 'bluetooth_address' )->nullable();
                $table->string( 'printJobs' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'printers' );
        }
    };
