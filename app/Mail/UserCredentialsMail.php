<?php

    namespace App\Mail;

    use App\Models\User;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;

    class UserCredentialsMail extends Mailable
    {
        use Queueable , SerializesModels;

        public $user;
        public $password;
        public $pin;

        public function __construct(User $user , string $password, string $pin = null)
        {
            $this->user     = $user;
            $this->password = $password;
            $this->pin      = $pin;
        }

        public function envelope() : Envelope
        {
            return new Envelope(
                subject: 'Your Account Credentials' ,
            );
        }

        public function content() : Content
        {
            return new Content(
                view: 'emails.user_credentials' ,
            );
        }

        public function attachments() : array
        {
            return [];
        }
    }
