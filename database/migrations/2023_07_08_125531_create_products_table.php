<?php

    use App\Enums\Activity;
    use App\Enums\Ask;
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
            Schema::create( 'products' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->string( 'slug' )->unique();
                $table->string( 'sku' )->unique();
                $table->foreignId( 'product_category_id' )->nullable()->constrained( 'product_categories' );
                $table->foreignId( 'product_brand_id' )->nullable()->constrained( 'product_brands' );
                $table->foreignId( 'barcode_id' )->nullable()->constrained( 'barcodes' );
                $table->foreignId( 'unit_id' )->nullable()->constrained( 'units' );
                $table->decimal( 'buying_price' , 13 , 6 )->default( 0 );
                $table->decimal( 'selling_price' , 13 , 6 )->default( 0 );
                $table->decimal( 'variation_price' , 13 , 6 )->default( 0 );
                $table->tinyInteger( 'status' )->default( Status::ACTIVE );
                $table->bigInteger( 'order' )->default( 1 );
                $table->tinyInteger( 'can_purchasable' )->default( Ask::YES );
                $table->tinyInteger( 'show_stock_out' )->default( Activity::ENABLE );
                $table->unsignedBigInteger( 'maximum_purchase_quantity' )->default( 1 );
                $table->unsignedBigInteger( 'low_stock_quantity_warning' )->default( 1 );
                $table->string( 'weight' )->nullable();
                $table->tinyInteger( 'refundable' )->default( Ask::YES );
                $table->longText( 'description' )->nullable();
                $table->unsignedTinyInteger( 'add_to_flash_sale' )->default( Ask::NO );
                $table->decimal( 'discount' , 13 , 6 )->default( 0 );
                $table->dateTime( 'offer_start_date' )->nullable();
                $table->dateTime( 'offer_end_date' )->nullable();
                $table->string( 'creator_type' )->nullable();
                $table->bigInteger( 'creator_id' )->nullable();
                $table->string( 'editor_type' )->nullable();
                $table->bigInteger( 'editor_id' )->nullable();
                $table->integer( 'is_stockable' )->default( Ask::NO );
                $table->decimal( 'overall_cost' , 10 )->default( 0 );
                $table->softDeletes();
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::dropIfExists( 'products' );
        }
    };