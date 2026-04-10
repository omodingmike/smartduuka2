<?php

    namespace App\Http\Requests\Cashflow;

    use Illuminate\Foundation\Http\FormRequest;

    class CompanyRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'company_name'  => [ 'required' , 'string' , 'max:190' ] ,
                'company_email' => [ 'required' , 'email' , 'max:190' ] ,
                'company_phone' => [ 'required' , 'string' , 'max:20' ] ,
            ];
        }
    }