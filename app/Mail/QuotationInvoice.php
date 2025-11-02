<?php

    namespace App\Mail;

    use App\Models\Order;
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Attachment;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;

    class QuotationInvoice extends Mailable
    {
        use Queueable , SerializesModels;

        public function __construct(public Order $order) {}

        public function envelope() : Envelope
        {
            return new Envelope(
                subject: orderName($this->order) ,
            );
        }

        public function content() : Content
        {
            return new Content(
                view: 'emails.invoice' ,
            );
        }

        public function attachments() : array
        {
            $name = orderName($this->order);
            return [
                Attachment::fromPath(storage_path("app/reports/$name.pdf"))
                          ->as("$name.pdf")
                          ->withMime('application/pdf')
            ];
        }
    }
