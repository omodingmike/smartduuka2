<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class PaymentMethodResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {

            return [
                'id'               => $this->id ,
                'name'             => $this->name ,
                'image'            => $this->image ,
                'merchant_code'    => $this->merchant_code ,
                'balance'          => $this->balance ,
                'transactions'     => $this->when(
                    $this->relationLoaded( 'transactions' ) ,
                    function () {
                        return PaymentMethodTransactionResource::collection( $this->transactions->take( 5 ) );
                    }
                ) ,
                'total_in'         => currency( $this->total_in ) ,
                'total_out'        => currency( abs( $this->total_out ) ) ,
                'balance_currency' => AppLibrary::currencyAmountFormat( $this->balance )
            ];
        }
    }
