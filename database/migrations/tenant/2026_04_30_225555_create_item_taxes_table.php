<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'item_taxes' , function (Blueprint $table) {
                $table->id();
                $table->morphs( 'item' );
                $table->foreignId('tax_id')->nullable()->constrained()->cascadeOnDelete();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'item_taxes' );

        }
    };
