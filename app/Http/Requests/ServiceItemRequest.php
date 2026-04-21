<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ServiceItemRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'item_id'    => [ 'required' , 'integer' ] ,
                'item_type'  => [ 'required' ] ,
                'quantity'   => [ 'required' , 'decimal:2' ] ,
                'price_id'   => [ 'required' , 'integer' ] ,
                'price_type' => [ 'required' ] ,
                'total'      => [ 'required' , 'decimal:2' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
