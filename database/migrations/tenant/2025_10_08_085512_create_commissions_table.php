<?php

    use App\Enums\CommissionType;
    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'commissions' , function (Blueprint $table) {
                $table->id();
                $table->string( 'name' );
                $table->unsignedTinyInteger( 'commission_type' )->default( CommissionType::PERCENTAGE );
                $table->decimal( 'commission_value' , 10 );
                $table->string( 'applies_to' )->default( 'all' );
                $table->string( 'product_scope' )->default( 'all_products' );
                $table->json( 'condition_json' )->nullable();
                $table->unsignedTinyInteger( 'is_active' )->default( Status::ACTIVE );
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'commissions' );
        }
    };
