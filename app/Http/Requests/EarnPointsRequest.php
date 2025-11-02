<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class EarnPointsRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'points' => 'required|integer' ,
            ];
        }
    }
