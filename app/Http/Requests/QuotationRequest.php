<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class QuotationRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'customer_id'  => [ 'required' , 'numeric' ] ,
                'date'         => [ 'required' , 'date' ] ,
                'expiry_date'  => [ 'required' , 'date' ] ,
                'notes'        => [ 'nullable' , 'string' ] ,
                'items'        => [ 'required' , 'string' ] ,
                'subtotal'     => [ 'required' , 'numeric' ] ,
                'tax'          => [ 'required' , 'numeric' ] ,
                'total'        => [ 'required' , 'numeric' ] ,
                'order_type'   => [ 'required' , 'numeric' ] ,
                'received'     => [ 'nullable' , 'numeric' ] ,
                'change'       => [ 'nullable' , 'numeric' ] ,
                'payment_type' => [ 'nullable' , 'string' ] ,
                'reference'    => [ 'nullable' , 'string' ] ,
            ];
        }
    }
