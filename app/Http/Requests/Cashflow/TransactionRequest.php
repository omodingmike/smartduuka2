<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class TransactionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'date'                    => [ 'sometimes' , 'date' ] ,
                'cash_type'               => [ 'sometimes' , 'integer' ] ,
                'entity_id'               => [ 'sometimes' , 'exists:entities,id' ] ,
                'amount'                  => [ 'sometimes' , 'numeric' ] ,
                'fee'                     => [ 'sometimes' , 'numeric' ] ,
                'currency_id'             => [ 'sometimes' , 'exists:currencies,id' ] ,
                'accountable_id'          => [ 'sometimes' , 'integer' ] ,
                'accountable_type'        => [ 'sometimes' , 'string' ] ,
                'description'             => [ 'sometimes' , 'string' ] ,
                'account_id'             => [ 'sometimes' , 'string:' ] ,
                'exchange_rate'           => [ 'nullable' , 'numeric' ] ,
                'transaction_category_id' => [ 'sometimes' , 'exists:transaction_categories,id' ] ,
                'status'                  => [ 'sometimes' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
