<?php

    namespace App\Jobs;

    use App\Http\Controllers\WhatsAppController;
    use App\Models\Order;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;

    class SendWhatsappQuotation implements ShouldQueue
    {
        use Queueable;

        public function __construct(public Order $order , public string $tenant) {}

        public function handle(WhatsAppController $whatsAppController) : void
        {
            $this->order->load(
                [
                    'orderProducts.item' ,
                    'creditDepositPurchases.paymentMethod' ,
                    'orderProducts.product.taxes.tax' ,
                    'orderProducts.product.unit:id,code' ,
                    'orderProducts.product.sellingUnits:id,code' ,
                    'user.addresses' , 'stocks' , 'user' , 'creator' , 'paymentMethods.paymentMethod'
                ] );
            $whatsAppController->sendQuotationNotification( $this->order , $this->tenant );
        }
    }
