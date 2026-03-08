<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PosOrderRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'customer_id' => [ 'required' , 'numeric' ] ,
                'subtotal'    => [ 'required' , 'numeric' ] ,
                'tax'         => [ 'required' , 'numeric' ] ,
                'total'       => [ 'required' , 'numeric' ] ,
                'items'       => [ 'required' , 'string' ] ,
                'received'    => [ 'required' , 'numeric:' ] ,
                'debt_amount' => [ 'sometimes' , 'numeric:' ] ,
                'change'      => [ 'required' , 'numeric:' ] ,
                'discount'    => [ 'sometimes' , 'numeric:' ] ,
                'payments'    => [ 'required' , 'string:' ] ,
                'order_type'  => [ 'required' , 'numeric::' ] ,
                'paymentType' => [ 'required' , 'numeric::' ] ,
                'channel'     => [ 'required' , 'numeric::' ] ,
            ];
        }
    }
