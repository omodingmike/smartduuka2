<?php

    namespace App\Mail;

    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Address;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;

    class SendEmails extends Mailable
    {
        use Queueable , SerializesModels;

        public function __construct(public string $subj , public mixed $data , public string $template) {}

        public function envelope() : Envelope
        {
            return new Envelope(
                from: new Address( config( 'mail.from.address' ) , "Smartduuka" ) ,
                subject: $this->subj ,
            );
        }


        public function content() : Content
        {
            return new Content(
                view: $this->template ,
                with: is_array( $this->data ) ? $this->data : [] ,
//                view: 'tenants.WelcomeToSmartduukaTemplate' ,

            );
        }


        public function attachments() : array
        {
            return [];
        }
    }
