<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'suppliers' , function (Blueprint $table) {
                $table->string( 'company' )->nullable()->change();
                $table->string( 'tin' )->nullable();
                $table->unsignedTinyInteger( 'status' )->default( Status::ACTIVE );
//                $table->unsignedTinyInteger( 'show_tax' )->default( 0 );
            } );
        }

        public function down() : void
        {
            Schema::table( 'suppliers' , function (Blueprint $table) {
                $table->dropColumn(['tin', 'status']);
                DB::table('suppliers')->whereNull('company')->update(['company' => '']);
                $table->string('company')->nullable(false)->change();
            } );
        }
    };
