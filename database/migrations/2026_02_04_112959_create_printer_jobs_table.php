<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'printer_jobs' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'printer_id' )->constrained( 'printers' )->cascadeOnDelete();
                $table->foreignId( 'printer_template_id' )->constrained( 'printer_templates' )->cascadeOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'printer_jobs' );
        }
    };
