<?php

    use App\Enums\Status;
    use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('buying_price');
            $table->string('unit');
            $table->integer('quantity');
            $table->integer('quantity_alert');
            $table->tinyInteger('status')->default(Status::ACTIVE)->comment(Status::ACTIVE . '=' . trans('statuse.' . Status::ACTIVE) . ', ' . Status::INACTIVE . '=' . trans('statuse.' . Status::INACTIVE));
            $table->string('buying_price')->nullable()->change();
            $table->string('quantity')->nullable()->change();
            $table->string('quantity_alert')->nullable()->change();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ingredients');

    }
};
