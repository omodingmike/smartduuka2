<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::table('credit_deposit_purchases' , function (Blueprint $table) {
                $table->unsignedBigInteger('payment_method_id')->nullable();
            });
        }

        public function down() : void
        {
            Schema::table('credit_deposit_purchases' , function (Blueprint $table) {
                $table->dropColumn('payment_method_id');
            });
        }
    };
