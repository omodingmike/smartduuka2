<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class RegisterRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'opening_float' => [ 'required' , 'numeric' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
