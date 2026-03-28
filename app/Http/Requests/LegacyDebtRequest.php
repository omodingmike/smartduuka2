<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class LegacyDebtRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'debts' => [ 'required' , 'string' ]
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
