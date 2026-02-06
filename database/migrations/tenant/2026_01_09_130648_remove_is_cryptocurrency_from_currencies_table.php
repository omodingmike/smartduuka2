<?php

    use App\Enums\Ask;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'currencies' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'is_cryptocurrency' )->default( Ask::YES )->change();
                $table->unsignedTinyInteger( 'is_base' )->default( Ask::NO );
            } );
        }

        public function down() : void
        {
            Schema::table( 'currencies' , function (Blueprint $table) {
                $table->unsignedTinyInteger( 'is_cryptocurrency' )->default( Ask::YES )->change();
                $table->dropColumn( 'is_base' );
            } );
        }
    };
