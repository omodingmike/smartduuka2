<?php

    namespace App\Jobs;

    use App\Http\Controllers\WhatsAppController;
    use App\Models\Order;
    use App\Services\PdfExportService;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class SendWhatsappQuotation implements ShouldQueue
    {
        use Queueable;

        public function __construct(public Order $order) {}

        public function handle(WhatsAppController $whatsAppController , PdfExportService $pdfExportService) : void
        {
            $whatsAppController->sendOrderPdf( $this->order , $pdfExportService );
        }
    }
