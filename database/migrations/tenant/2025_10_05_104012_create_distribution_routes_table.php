<?php

    use App\Enums\DistributionStockStatus;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up() : void
        {
            Schema::create( 'distributionRoutes' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'user_id' )->constrained();
                $table->decimal( 'route_value' , 20 );
                $table->decimal( 'actual_sales' , 20 )->default( 0 );
                $table->string( 'stock_batch' );
                $table->unsignedSmallInteger( 'status' )->default( DistributionStockStatus::OUTSTANDING );
                $table->timestamps();
            } );
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down() : void
        {
            Schema::dropIfExists( 'distributionRoutes' );
        }
    };
