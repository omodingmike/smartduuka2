<?php

    use App\Enums\PurchasePaymentStatus;
    use App\Enums\PurchaseStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::create('purchases' , function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained();
                $table->timestamp('date');
                $table->string('reference_no')->nullable();
                $table->decimal('tax' , 13 , 6)->nullable()->default(0);
                $table->decimal('discount' , 13 )->nullable()->default(0);
                $table->decimal('subtotal' , 20 );
                $table->decimal('total' , 20 )->default(0);
                $table->unsignedTinyInteger('status')->default(PurchaseStatus::RECEIVED);
                $table->unsignedTinyInteger('payment_status')->default(PurchasePaymentStatus::PENDING);
                $table->text('note')->nullable();
                $table->string('creator_type')->nullable();
                $table->bigInteger('creator_id')->nullable();
                $table->string('editor_type')->nullable();
                $table->bigInteger('editor_id')->nullable();
                $table->integer('type')->default(1);
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists('purchases');
        }
    };
