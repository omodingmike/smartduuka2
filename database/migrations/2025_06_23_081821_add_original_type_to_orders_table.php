<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('orders' , function (Blueprint $table) {
                $table->string('original_type')->nullable()->after('order_type');
                $table->dateTime('due_date')->nullable()->after('original_type');
            });
        }

        public function down() : void
        {
            Schema::table('orders' , function (Blueprint $table) {
                $table->dropColumn('original_type');
                $table->dropColumn('due_date');
            });
        }
    };
