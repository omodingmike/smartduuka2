<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerPaymentRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'amount'        => 'required' ,
                'date'          => 'required' ,
                'paymentMethod' => 'required' ,
            ];
        }
    }
