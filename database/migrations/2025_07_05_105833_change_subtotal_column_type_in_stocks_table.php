<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->decimal('subtotal' , 20 , 6)->default(0)->change();
                $table->decimal('total' , 20 , 6)->default(0)->change();
            });
        }

        public function down() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->decimal('subtotal' , 19 , 6)->default(0)->change();
                $table->decimal('total' , 19 , 6)->default(0)->change();
            });
        }
    };
