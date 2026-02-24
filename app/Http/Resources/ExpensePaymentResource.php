<?php

    namespace App\Http\Resources;

    use App\Models\ExpensePayment;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin ExpensePayment */
    class ExpensePaymentResource extends JsonResource
    {

        public function toArray(Request $request) : array
        {
            return [
                'id'            => $this->id ,
                'date'          => datetime( $this->date ) ,
                'amount'        => currency( $this->amount ) ,
                'paymentMethod' => new PaymentMethodResource ( $this->method ) ,
                'attachment'    => $this->attachment ,
            ];
        }
    }
