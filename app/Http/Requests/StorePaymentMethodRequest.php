<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StorePaymentMethodRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'          => 'required|string' ,
                'merchant_code' => 'sometimes|string' ,
                'image'         => 'sometimes|file' ,
            ];
        }
    }
