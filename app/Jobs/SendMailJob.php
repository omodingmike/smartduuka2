<?php

    namespace App\Jobs;

    use App\Mail\SendEmail;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;
    use Illuminate\Support\Facades\Mail;

    class SendMailJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public array $data) {}

        public function handle() : void
        {
            Mail::to( app()->isLocal() ? 'omodingmike@gmail.com' : $this->data[ 'email' ] )
                ->send( new SendEmail( 'emails.newusertemplate' , 'Welcome Aboard!' , $this->data ) );
        }
    }
