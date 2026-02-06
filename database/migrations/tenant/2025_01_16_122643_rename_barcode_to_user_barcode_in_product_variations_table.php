<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('product_variations' , function (Blueprint $table) {
                $table->renameColumn('barcode' , 'user_barcode');
            });
        }

        public function down() : void
        {
            Schema::table('product_variations' , function (Blueprint $table) {
                $table->renameColumn('user_barcode' , 'barcode');
            });
        }
    };
