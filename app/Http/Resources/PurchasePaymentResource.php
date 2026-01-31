<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class PurchasePaymentResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'              => $this->id ,
                'purchase_id'     => $this->purchase_id ,
                'date'            => $this->date ,
                'converted_date'  => AppLibrary::datetime2( $this->date ) ,
                'reference_no'    => $this->reference_no ,
                'payment_method'  => $this->paymentMethod ? new PaymentMethodResource( $this->paymentMethod ) : null ,
                'amount'          => AppLibrary::flatAmountFormat( $this->amount ) ,
                'amount_currency' => AppLibrary::currencyAmountFormat( $this->amount ) ,
            ];
        }
    }
