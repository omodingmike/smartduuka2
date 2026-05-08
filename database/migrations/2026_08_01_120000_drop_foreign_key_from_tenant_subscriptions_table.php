<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->dropForeign( [ 'tenant_id' ] );
            } );
        }

        public function down() : void
        {
            Schema::table( 'tenant_subscriptions' , function (Blueprint $table) {
                $table->foreign( 'tenant_id' )->references( 'id' )->on( 'tenants' )->onUpdate( 'cascade' )->onDelete( 'cascade' );
            } );
        }
    };
