<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\CustomerPayment;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin CustomerPayment */
    class CustomerPaymentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'               => $this->id ,
                'date'             => AppLibrary::date( $this->date ) ,
                'amount'           => currency( $this->amount ) ,
                'balance'          => $this->balance ,
                'balance_currency' => currency( $this->balance ) ,
                'payment_method'   => $this->paymentMethod->name ,
            ];
        }
    }
