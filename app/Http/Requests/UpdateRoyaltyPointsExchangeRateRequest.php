<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateRoyaltyPointsExchangeRateRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'value'  => 'required|integer' ,
                'points' => 'required|integer' ,
            ];
        }
    }
