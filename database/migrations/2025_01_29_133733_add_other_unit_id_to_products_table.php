<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {

        public function up() : void
        {
            Schema::table('products' , function (Blueprint $table) {
                $table->unsignedBigInteger('other_unit_id')->nullable()->after('unit_id');
                $table->unsignedBigInteger('units_nature')->nullable()->after('other_unit_id');
                $table->foreign('other_unit_id')->references('id')->on('units')->onDelete('cascade');
            });
        }

        public function down() : void
        {
            Schema::table('products' , function (Blueprint $table) {
                $table->dropForeign([ 'other_unit_id' ]);
                $table->dropColumn('other_unit_id');
                $table->dropColumn('units_nature');
            });
        }
    };
