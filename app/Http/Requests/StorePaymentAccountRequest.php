<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StorePaymentAccountRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'        => [ 'required' , 'string' ] ,
                'currency_id' => [ 'required' , 'numeric' ] ,
            ];
        }
    }
