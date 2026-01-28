<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class PosPaymentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'date'            => AppLibrary::datetime2( $this->date ) ,
                'reference_no'    => $this->reference_no ,
                'amount'          => $this->amount ,
                'order'           => $this->order_id ,
                'amount_currency' => AppLibrary::currencyAmountFormat( $this->amount ) ,
                'payment_method'  => new PaymentMethodResource( $this->paymentMethod ) ,
            ];
        }
    }
