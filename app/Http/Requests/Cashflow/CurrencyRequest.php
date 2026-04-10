<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class CurrencyRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'    => [ 'required' ] ,
                'symbol'  => [ 'required' ] ,
                'foreign' => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
