<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PurchaseReturnRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'supplier_id' => [ 'required' , 'exists:suppliers' ] ,
                'purchase_id' => [ 'required' , 'exists:purchases' ] ,
                'date'        => [ 'required' , 'date' ] ,
                'debit_note'  => [ 'required' ] ,
                'notes'       => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
