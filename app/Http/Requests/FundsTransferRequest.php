<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class FundsTransferRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'from_method_id' => 'required|numeric:' ,
                'to_method_id'   => 'required|numeric:' ,
                'amount'         => 'required|numeric:' ,
                'charge'         => 'required|numeric:' ,
                'description'    => 'required|string:' ,
            ];
        }
    }
