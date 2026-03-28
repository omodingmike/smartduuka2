<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerPaymentRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'amount' => 'required|numeric:' ,
                'method' => 'required|numeric:' ,
            ];
        }
    }
