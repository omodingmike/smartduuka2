<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\PaymentMethodTransaction;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin PaymentMethodTransaction */
    class PaymentMethodTransactionResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                => $this->id ,
                'amount'            => $this->amount ,
                'amount_currency'   => AppLibrary::currencyAmountFormat( $this->amount ) ,
                'charge'            => $this->charge ,
                'charge_currency'   => currency( $this->charge ) ,
                'description'       => $this->description ,
                'created_at'        => datetime( $this->created_at ) ,
                'payment_method_id' => $this->payment_method_id ,
            ];
        }
    }
