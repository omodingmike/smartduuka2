<?php

    namespace App\Jobs;

    use App\Mail\UserCredentialsMail;
    use App\Models\User;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Mail;

    class SendUserCredentialsJob implements ShouldQueue
    {
        use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

        protected $user;
        protected $password;
        protected $pin;

        public function __construct(User $user , string $password, string $pin = null)
        {
            $this->user     = $user;
            $this->password = $password;
            $this->pin      = $pin;
        }

        public function handle() : void
        {
            Mail::to( app()->isLocal() ? 'omodingmike@gmail.com' : $this->user->email )->send( new UserCredentialsMail( $this->user , $this->password, $this->pin ) );
        }
    }
