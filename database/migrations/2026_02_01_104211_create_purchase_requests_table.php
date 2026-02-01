<?php

    use App\Enums\PurchaseRequestStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'stock_purchase_requests' , function (Blueprint $table) {
                $table->id();
                $table->string( 'requester_name' );
                $table->string( 'reference' );
                $table->unsignedTinyInteger( 'department' );
                $table->unsignedTinyInteger( 'priority' );
                $table->dateTime( 'date' );
                $table->string( 'reason' );
                $table->unsignedTinyInteger( 'status' )->default( PurchaseRequestStatus::PENDING );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'stock_purchase_requests' );
        }
    };
