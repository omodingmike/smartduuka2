<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerLedgerRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'date'        => [ 'required' , 'date' ] ,
                'reference'   => [ 'required' ] ,
                'description' => [ 'required' ] ,
                'bill_amount' => [ 'required' , 'decimal:2' ] ,
                'paid'        => [ 'required' , 'decimal:2' ] ,
                'balance'     => [ 'required' , 'decimal:2' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
