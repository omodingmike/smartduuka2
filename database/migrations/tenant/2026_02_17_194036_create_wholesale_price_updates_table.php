<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'wholesale_price_updates' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'purchase_id' )->constrained( 'purchases' )->nullOnDelete();
                $table->unsignedInteger( 'min_quantity' );
                $table->decimal( 'old_price' );
                $table->decimal( 'new_price' );
                $table->unsignedBigInteger( 'item_id' );
                $table->string( 'item_type' );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'wholesale_price_updates' );
        }
    };
