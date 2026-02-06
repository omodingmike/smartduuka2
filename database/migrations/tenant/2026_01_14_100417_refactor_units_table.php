<?php

    use App\Traits\TableSchemaTrait;
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        use TableSchemaTrait;

        /**
         * Run the migrations.
         */
        public function up() : void
        {
            Schema::table( 'units' , function (Blueprint $table) {
                $this->dropColumnIfExists( 'units' , 'code' );
                $this->dropColumnIfExists( 'units' , 'creator_type' );
                $this->dropColumnIfExists( 'units' , 'creator_id' );
                $this->dropColumnIfExists( 'units' , 'editor_type' );
                $this->dropColumnIfExists( 'units' , 'editor_id' );
                $this->dropColumnIfExists( 'units' , 'short_name' );
                $this->dropColumnIfExists( 'units' , 'conversion_factor' );
                $this->dropColumnIfExists( 'units' , 'base_unit_id' );
                $table->string( 'short_name' );
                $table->unsignedInteger( 'conversion_factor' );
                $table->foreignId( 'base_unit_id' )->nullable()->references( 'id' )->on( 'units' )->onDelete( 'cascade' );
            } );
        }

        /**
         * Reverse the migrations.
         */
        public function down() : void
        {
            Schema::table( 'units' , function (Blueprint $table) {
                $this->dropColumnIfExists( 'units' , 'code' );
                $this->dropColumnIfExists( 'units' , 'creator_type' );
                $this->dropColumnIfExists( 'units' , 'creator_id' );
                $this->dropColumnIfExists( 'units' , 'editor_type' );
                $this->dropColumnIfExists( 'units' , 'editor_id' );
                $this->dropColumnIfExists( 'units' , 'short_name' );
                $this->dropColumnIfExists( 'units' , 'conversion_factor' );
                $this->dropColumnIfExists( 'units' , 'base_unit_id' );
            } );
        }
    };
