<?php

    use App\Enums\Ask;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'services' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'tax_inclusive' )->default( Ask::NO );
            } );
        }


        public function down() : void
        {
            Schema::table( 'services' , function (Blueprint $table) {
                $table->dropColumn( 'tax_inclusive' );
            } );
        }
    };
