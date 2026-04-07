<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('printer_templates', function (Blueprint $table) {
            $table->string('document_type')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('printer_templates', function (Blueprint $table) {
            $table->dropColumn('document_type');
        });
    }
};
