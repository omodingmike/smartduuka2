<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'expense_categories' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->integer( 'user_id' );
                $table->string( 'description' )->nullable()->after( 'status' );
                $table->unsignedBigInteger( 'parent_id' )->nullable();
                $table->integer( 'status' )->nullable()->default( 0 );
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'expense_categories' );
        }
    };
