<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class CustomerPaymentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'date'           => AppLibrary::date($this->date) ,
                'amount'         => AppLibrary::currencyAmountFormat($this->amount) ,
                'payment_method' => $this->paymentMethod->name ,
            ];
        }
    }
