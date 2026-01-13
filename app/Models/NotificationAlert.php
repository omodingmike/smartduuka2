<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationAlert extends Model
{
    use HasFactory;
    protected $table = "notification_alerts";
    
    protected $fillable = [
        'event_key', 
        'category', 
        'label', 
        'description', 
        'email', 
        'sms', 
        'whatsapp', 
        'system',
        'mail_message',
        'sms_message',
        'push_notification_message'
    ];

    protected $casts = [
        'email'    => 'boolean',
        'sms'      => 'boolean',
        'whatsapp' => 'boolean',
        'system'   => 'boolean',
    ];
}
