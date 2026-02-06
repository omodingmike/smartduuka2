<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('products' , function (Blueprint $table) {
                $table->unsignedInteger('retail_unit_id')->after('unit_id')->nullable();
                $table->unsignedInteger('mid_unit_id')->after('unit_id')->nullable();
                $table->unsignedInteger('top_unit_id')->after('unit_id')->nullable();
                $table->decimal('units_per_mid_unit')->after('unit_id')->nullable();
                $table->decimal('mid_units_per_top_unit')->after('unit_id')->nullable();
                $table->decimal('base_units_per_top_unit')->after('unit_id')->nullable();
                $table->decimal('retail_price_per_base_unit' , 16)->after('unit_id')->nullable();
                $table->decimal('mid_unit_wholesale_price' , 16)->after('unit_id')->nullable();
                $table->decimal('top_unit_wholesale_price' , 16)->after('unit_id')->nullable();
            });
        }

        public function down() : void
        {
            Schema::table('products' , function (Blueprint $table) {
                $table->dropColumn([
                    'retail_unit_id' ,
                    'mid_unit_id' ,
                    'top_unit_id' ,
                    'retail_price_per_base_unit' ,
                    'mid_unit_wholesale_price' ,
                    'units_per_mid_unit' ,
                    'mid_units_per_top_unit' ,
                    'top_unit_wholesale_price'
                ]);
            });
        }
    };
