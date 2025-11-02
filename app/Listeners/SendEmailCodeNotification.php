<?php

namespace App\Listeners;

use App\Events\SendEmailCode;
use App\Mail\SendOtp;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailCodeNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\SendEmailCode $event
     * @return void
     */
    public function handle(SendEmailCode $event) : void
    {
        try {
            Mail::to($event->info['email'])->send(new SendOtp($event->info['pin']));
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }
}