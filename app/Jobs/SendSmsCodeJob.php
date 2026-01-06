<?php

    namespace App\Jobs;

    use App\Helpers\SMS;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class SendSmsCodeJob implements ShouldQueue
    {
        use Queueable , SMS;

        /**
         * Create a new job instance.
         */
        public function __construct(public array $data)
        {
            //
        }

        /**
         * Execute the job.
         */
        public function handle() : void
        {
            //[ 'phone' => $request->post( 'phone' ) , 'code' => $request->post( 'country_code' ) , 'token' => $token ]
            $token = $this->data[ 'token' ];
            $data  = [
                'to'      => $this->data[ 'phone' ] ,
                'message' => "OTP verification code $token"
            ];
            $this->send( $data );
        }
    }
