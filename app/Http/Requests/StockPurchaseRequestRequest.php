<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StockPurchaseRequestRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'requester_name' => [ 'required' ] ,
                'department'     => [ 'required' , 'integer' ] ,
                'priority'       => [ 'required' , 'integer' ] ,
                'supplier_id'    => [ 'required' , 'numeric:' ] ,
                'reason'         => [ 'required' ] ,
                'items'          => [ 'required' , 'string' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
