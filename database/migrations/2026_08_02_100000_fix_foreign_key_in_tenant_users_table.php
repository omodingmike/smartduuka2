<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            // Drop the incorrect foreign key constraint from the original migration
            $table->dropForeign('tenant_users_global_user_id_foreign');

            // Add the correct foreign key constraint referencing the central_users table
            $table->foreign('global_user_id')
                  ->references('global_id')
                  ->on('central_users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_users', function (Blueprint $table) {
            // Drop the correct foreign key constraint
            $table->dropForeign('tenant_users_global_user_id_foreign');

            // Re-add the original, incorrect foreign key constraint for rollback
            $table->foreign('global_user_id')
                  ->references('global_id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }
};
