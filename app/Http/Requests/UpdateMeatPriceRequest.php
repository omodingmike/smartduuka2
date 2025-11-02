<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateMeatPriceRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'adults'         => 'required|numeric' ,
                'five_to_nine'   => 'required|numeric' ,
                'less_than_five' => 'required|numeric' ,
            ];
        }
    }
