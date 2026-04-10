<?php

    use App\Enums\TransactionStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'transactions' , function (Blueprint $table) {
                $table->id();
                $table->string( 'reference' );
                $table->dateTime( 'date' );
                $table->unsignedTinyInteger( 'cash_type' );
                $table->foreignId( 'entity_id' )->constrained( 'entities' );
                $table->decimal( 'amount' , 20 );
                $table->decimal( 'fee' , 20 )->nullable()->default( 0 );
                $table->decimal( 'cash_in' , 20 )->nullable()->default( 0 );
                $table->decimal( 'cash_out' , 20 )->nullable()->default( 0 );
                $table->decimal( 'exchange_rate' )->nullable()->default( 1);
                $table->foreignId( 'currency_id' )->constrained( 'currencies' );
                $table->foreignId( 'transaction_category_id' )->constrained( 'transaction_categories' );
                $table->morphs( 'accountable' );
                $table->string( 'description' )->nullable();
                $table->unsignedTinyInteger( 'status' )->default( TransactionStatus::DRAFT );
                $table->timestamps();
                $table->softDeletes();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'transactions' );
        }
    };
