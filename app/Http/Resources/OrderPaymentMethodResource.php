<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\OrderPaymentMethod;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin OrderPaymentMethod */
    class OrderPaymentMethodResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'              => $this->id ,
                'amount'          => $this->amount ,
                'amount_currency' => AppLibrary:: currencyAmountFormat( $this->amount ) ,
                'paymentMethod'   => new PaymentMethodResource( $this->paymenMethod ) ,
            ];
        }
    }
