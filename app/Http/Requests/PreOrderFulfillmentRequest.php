<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class PreOrderFulfillmentRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'action'                         => [ 'required' , Rule::in( [ 'HONOR' , 'TOP_UP' , 'PRORATE' ] ) ] ,
                'topUpAmount'                    => [ 'required_if:action,TOP_UP' , 'numeric' , 'min:0' ] ,
                'paymentMethod'                  => [ 'required_if:action,TOP_UP' , 'string' ] ,
                'proratedItems'                  => [ 'required_if:action,PRORATE' , 'array' ] ,
                'proratedItems.*.orderProductId' => [ 'required_if:action,PRORATE' , 'integer' , 'exists:order_products,id' ] ,
                'proratedItems.*.newQty'         => [ 'required_if:action,PRORATE' , 'integer' , 'min:0' ] ,
            ];
        }
    }
