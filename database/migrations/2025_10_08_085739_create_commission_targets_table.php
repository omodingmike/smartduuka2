<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        public function up() : void
        {
            Schema::create( 'commission_targets' , function (Blueprint $table) {
                $table->id();
                $table->foreignId( 'commission_id' )->constrained( 'commissions' )->cascadeOnDelete();
                $table->foreignId( 'user_id' )->nullable()->constrained( 'users' )->nullOnDelete();
                $table->foreignId( 'role_id' )->nullable()->constrained( 'roles' )->nullOnDelete();
                $table->foreignId( 'product_id' )->nullable()->constrained( 'products' )->nullOnDelete();
                $table->foreignId( 'product_variation_id' )->nullable()->constrained( 'product_variations' )->nullOnDelete();
                $table->string( 'variation_label' )->nullable();
                $table->timestamps();
            } );
        }

        public function down() : void
        {
            Schema::dropIfExists( 'commission_targets' );
        }
    };
