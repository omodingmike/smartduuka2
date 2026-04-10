<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class SubAccountRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name' => [ 'required' , 'unique:sub_accounts,name' ] ,
                'type' => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
