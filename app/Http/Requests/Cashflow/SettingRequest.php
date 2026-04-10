<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class SettingRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'companyName'       => 'sometimes|string|max:100' ,
                'companyEmail'      => 'sometimes|email|max:100' ,
                'companyPhone'      => 'sometimes|string|max:20' ,
                'default_currency'  => 'sometimes|numeric:|max:20' ,
                'approvalThreshold' => 'sometimes|numeric' ,
            ];
        }
    }
