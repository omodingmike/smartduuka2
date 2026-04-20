<?php

    use App\Enums\QuotationStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'quotation_status' )->default( QuotationStatus::PENDING );
            } );
        }

        public function down() : void
        {
            Schema::table( 'orders' , function (Blueprint $table) {
                $table->dropColumn( 'quotation_status' );
            } );
        }
    };
