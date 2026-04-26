<?php

    namespace App\Jobs;

    use App\Enums\QuotationType;
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
            $relations = [
                'creditDepositPurchases.paymentMethod' ,
                'user.addresses' ,
                'stocks' ,
                'user' ,
                'creator' ,
                'paymentMethods.paymentMethod'
            ];

            if ( $this->order->quotation_type === QuotationType::SERVICE ) {
                $relations[] = 'orderServiceProducts.service';
            }
            else {
                $relations[] = 'orderProducts.item';
                $relations[] = 'orderProducts.product.taxes.tax';
                $relations[] = 'orderProducts.product.unit:id,code';
                $relations[] = 'orderProducts.product.sellingUnits:id,code';
            }

            $this->order->load( $relations );
            $whatsAppController->sendQuotationNotification( $this->order , $this->tenant );
        }
    }
