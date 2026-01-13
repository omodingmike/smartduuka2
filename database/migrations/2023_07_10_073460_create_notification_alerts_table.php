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
        Schema::create('notification_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique(); // Maps to 'id' in TS interface
            $table->string('category')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            
            // Channels
            $table->boolean('email')->default(false);
            $table->boolean('sms')->default(false);
            $table->boolean('whatsapp')->default(false);
            $table->boolean('system')->default(false);

            // Messages (Optional but likely needed for backend logic)
            $table->text('mail_message')->nullable();
            $table->text('sms_message')->nullable();
            $table->text('push_notification_message')->nullable(); // For system/push

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_alerts');
    }
};
