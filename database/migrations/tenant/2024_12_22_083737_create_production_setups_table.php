<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create('production_setups' , function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('product_id');
                $table->decimal('overall_cost' , 15 , 2)->default(0)->after('product_id');
                $table->timestamps();
            });
        }

        public function down() : void
        {
            Schema::dropIfExists('production_setups');
        }
    };
