<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->unsignedInteger('unit_id')->after('product_id')->nullable();
                $table->decimal('rate')->after('unit_id')->nullable();
                $table->datetime('expiry_date')->after('rate')->nullable();
            });
        }

        public function down() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->dropColumn('unit_id');
                $table->dropColumn('rate');
                $table->dropColumn('expiry_date');
            });
        }
    };
