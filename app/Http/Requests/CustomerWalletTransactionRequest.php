<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerWalletTransactionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'user_id'           => [ 'required' , 'exists:users' ] ,
                'amount'            => [ 'required' , 'numeric:' ] ,
                'payment_method_id' => [ 'required' , 'exists:payment_methods' ] ,
                'reference'         => [ 'required' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
