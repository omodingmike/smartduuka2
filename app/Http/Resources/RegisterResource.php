<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Register;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Register */
    class RegisterResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                      => $this->id ,
                'opening_float'           => $this->opening_float ,
                'opening_float_currency'  => AppLibrary::currencyAmountFormat( $this->opening_float ) ,
                'notes'                   => $this->notes ,
                'status'                  => [ 'label' => $this->status->label() , 'value' => $this->status?->value ] ,
                'expected_float'          => $this->expected_float ,
                'expected_float_currency' => AppLibrary::currencyAmountFormat( $this->expected_float ) ,
                'closing_float'           => $this->closing_float ,
                'closing_float_currency'  => AppLibrary::currencyAmountFormat( $this->closing_float ) ,
                'difference'              => $this->difference ,
                'difference_currency'     => AppLibrary::currencyAmountFormat( $this->difference ) ,
                'closed_at'               => $this->closed_at ,
                'created_at'              => AppLibrary::datetime2( $this->created_at ) ,
                'user_id'                 => $this->user_id ,
                'sales'                   => $this->posPayments()->sum( 'amount' ) ,
                'sales_currency'          => AppLibrary::currencyAmountFormat( $this->posPayments()->sum( 'amount' ) ) ,
                'user'                    => new UserResource( $this->whenLoaded( 'user' ) ) ,
                'posPayments'             => PosPaymentResource::collection( $this->posPayments ) ,
            ];
        }
    }
