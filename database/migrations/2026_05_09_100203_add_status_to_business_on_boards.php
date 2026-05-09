<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {

        public function up() : void
        {
            Schema::table( 'business_on_boards' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'status' )->default( Status::INACTIVE );
            } );
        }

        public function down() : void
        {
            Schema::table( 'business_on_boards' , function (Blueprint $table) {
                $table->dropColumn( 'status' );
            } );
        }
    };
