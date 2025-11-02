<?php

    namespace App\Jobs;

    use App\Mail\SendOtp;
    use Exception;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;

    class SendEmailCodeNotificationJob implements ShouldQueue
    {
        use Queueable;

        /**
         * Create a new job instance.
         */
        public function __construct(public array $info) {}

        /**
         * Execute the job.
         */
        public function handle() : void
        {
            try {
                Mail::to( $this->info[ 'email' ] )->send( new SendOtp( $this->info[ 'pin' ] ) );
            } catch ( Exception $e ) {
                Log::info( $e->getMessage() );
            }
        }
    }
