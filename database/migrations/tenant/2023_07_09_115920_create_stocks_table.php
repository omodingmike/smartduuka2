<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::create('stocks' , function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained();
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->string('item_type');
                $table->unsignedBigInteger('item_id');
                $table->string('variation_names')->nullable();
                $table->string('sku')->nullable();
                $table->decimal('price', 20)->default(0);
                $table->decimal('quantity')->default(1);
                $table->decimal('discount', 19 )->default(0);
                $table->decimal('subtotal', 20)->default(0);
                $table->decimal('total', 20)->default(0);
                $table->decimal('tax', 19)->default(0);
                $table->unsignedTinyInteger('status')->default(Status::INACTIVE);
                $table->integer('type')->default(1);
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists('stocks');
        }
    };
