<?php

    namespace App\Jobs;

    use App\Mail\QuotationInvoice;
    use App\Models\Order;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Queue\Middleware\WithoutOverlapping;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Support\Facades\Mail;

    class SendInvoiceMailJob implements ShouldQueue
    {
        use Dispatchable , InteractsWithQueue , Queueable , SerializesModels;

        public int $tries   = 5;
        public int $timeout = 60;
        public int $backoff = 3;

        public function __construct(public Order $order) {}

        public function middleware() : array
        {
            return [ new WithoutOverlapping($this->order->id) ];
        }

        public function handle() : void
        {
            Mail::to($this->order->user->email)->send(new QuotationInvoice($this->order));
        }
    }
