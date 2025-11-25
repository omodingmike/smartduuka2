<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CleaningOrderRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'cleaning_service_customer_id' => [ 'required' , 'numeric:' ] ,
                'total'                        => [ 'required' , 'numeric' ] ,
                'date'                         => [ 'required' , 'date' ] ,
                'service_method'               => [ 'required' , 'integer' ] ,
                'subtotal'                     => [ 'required' , 'numeric' ] ,
                'tax'                          => [ 'required' , 'numeric' ] ,
                'discount'                     => [ 'required' , 'numeric' ] ,
                'payment_method_id'            => [ 'required' , 'numeric:' ] ,
                'paid'                         => [ 'required' , 'numeric' ] ,
                'balance'                      => [ 'required' , 'numeric' ] ,
                'items'                        => [ 'required' , 'string' ] ,
                'address'                      => [ 'sometimes' , 'string' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
