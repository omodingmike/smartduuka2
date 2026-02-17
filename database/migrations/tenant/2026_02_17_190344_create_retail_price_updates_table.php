<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'retail_price_updates' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'unit_id' )->constrained( 'units' )->nullOnDelete();
                $table->foreignId( 'purchase_id' )->constrained( 'purchases' )->nullOnDelete();
                $table->bigInteger( 'item_id' );
                $table->string( 'item_type' );
                $table->decimal( 'old_price' );
                $table->decimal( 'new_price' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'retail_price_updates' );
        }
    };
