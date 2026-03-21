<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class OrderReturnRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }


        public function rules() : array
        {
            return [
                'exchangeItems' => 'required|string' ,
                'returnItems'   => 'required|string' ,
                'orderId'       => 'required|numeric:' ,
                'reason'        => 'required|string' ,
                'refundMethod'  => 'sometimes|numeric' ,
            ];
        }
    }
