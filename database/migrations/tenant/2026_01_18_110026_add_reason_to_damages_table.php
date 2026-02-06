<?php

    use App\Enums\DamageStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'damages' , function (Blueprint $table) {
                $table->string( 'reason' )->nullable();
                $table->foreignId( 'user_id' )->nullable()->references( 'id' )->on( 'users' )->cascadeOnDelete();
                $table->string( 'status' )->default( DamageStatus::Pending );
            } );
        }

        public function down() : void
        {
            Schema::table( 'damages' , function (Blueprint $table) {
                $table->dropForeign( [ 'user_id' ] );
                $table->dropColumn( [ 'reason' , 'user_id' ] );
                $table->dropColumn( [ 'status' ] );
            } );
        }
    };
