<?php

    namespace App\Jobs;

    use App\Mail\SendEmails;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;
    use Illuminate\Support\Facades\Mail;

    class SendEmailsJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public string $to , public string $subj , public string $template , public mixed $data = NULL) {}

        public function handle() : void
        {
            Mail::to( app()->isLocal() ? 'omodingmike@gmail.com' : $this->to )
                ->send( new SendEmails( $this->subj , $this->data , $this->template ) );
        }
    }
