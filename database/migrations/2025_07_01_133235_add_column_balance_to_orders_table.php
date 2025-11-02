<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('orders' , function (Blueprint $table) {
                $table->decimal('balance' , 16 , 2)->default(0)->after('paid');
            });
        }

        public function down() : void
        {
            Schema::table('orders' , function (Blueprint $table) {
                $table->dropColumn('balance');
            });
        }
    };
