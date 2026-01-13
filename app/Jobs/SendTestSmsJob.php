<?php

    namespace App\Jobs;

    use App\Helpers\SMS;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class SendTestSmsJob implements ShouldQueue
    {
        use Queueable , SMS;

        /**
         * Create a new job instance.
         */
        public function __construct(public string $to , public string $message) {}

        /**
         * Execute the job.
         */
        public function handle() : void
        {
            $this->send( [ 'to' => $this->to , 'message' => $this->message ] );
        }
    }
