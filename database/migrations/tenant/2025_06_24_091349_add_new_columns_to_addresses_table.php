<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('addresses' , function (Blueprint $table) {
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
            });
        }

        public function down() : void
        {
            Schema::table('addresses' , function (Blueprint $table) {
                $table->dropColumn('country_id');
                $table->dropColumn('state_id');
                $table->dropColumn('city_id');
            });
        }
    };
