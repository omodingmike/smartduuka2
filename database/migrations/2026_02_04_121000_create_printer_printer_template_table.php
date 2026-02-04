<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::dropIfExists( 'printer_jobs' );

            Schema::create( 'printer_jobs' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'printer_id' )->constrained( 'printers' )->onDelete( 'cascade' );
                $table->foreignId( 'printer_template_id' )->constrained( 'printer_templates' )->onDelete( 'cascade' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'printer_jobs' );
        }
    };
