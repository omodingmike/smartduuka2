<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->decimal('system_stock')->default(0);
                $table->decimal('physical_stock')->default(0);
                $table->bigInteger('difference')->default(0);
                $table->string('discrepancy')->nullable();
                $table->string('classification')->nullable();
                $table->unsignedInteger('creator')->nullable();
            });
        }

        public function down() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->dropColumn('system_stock');
                $table->dropColumn('physical_stock');
                $table->dropColumn('difference');
                $table->dropColumn('discrepancy');
                $table->dropColumn('classification');
                $table->dropColumn('creator');
            });
        }
    };
