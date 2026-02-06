<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'purchase_returns' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'supplier_id' )->constrained( 'suppliers' )->cascadeOnDelete();
                $table->foreignId( 'purchase_id' )->constrained( 'purchases' )->cascadeOnDelete();
                $table->dateTime( 'date' );
                $table->string( 'debit_note' );
                $table->string( 'notes' );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'purchase_returns' );
        }
    };
