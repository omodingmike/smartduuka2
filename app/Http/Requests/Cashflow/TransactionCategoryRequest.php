<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class TransactionCategoryRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'      => [ 'required' ] ,
                'cash_type' => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
