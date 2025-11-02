<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->unsignedBigInteger('source_warehouse_id')->nullable()->after('id');
                $table->unsignedBigInteger('destination_warehouse_id')->nullable()->after('source_warehouse_id');
                $table->longText('description')->nullable()->after('destination_warehouse_id');
            });
        }

        public function down() : void
        {
            Schema::table('stocks' , function (Blueprint $table) {
                $table->dropForeign('source_warehouse_id');
                $table->unsignedBigInteger('destination_warehouse_id');
                $table->dropColumn('source_warehouse_id');
            });
        }
    };
