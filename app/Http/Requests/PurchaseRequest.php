<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PurchaseRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'supplier_id' => [ 'required' , 'numeric' ] ,
                'date'        => [ 'required' , 'string' ] ,
                'refNo'       => [ 'sometimes' , 'string' ] ,
                'shipping'    => [ 'sometimes' , 'numeric:' ] ,
                'notes'       => [ 'sometimes' , 'string:' ] ,
                'status'      => [ 'required' , 'numeric' ] ,
                'items'       => [ 'required' , 'string' ] ,
            ];
        }
    }
