<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ServiceTierRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'       => [ 'required' ] ,
                'price'      => [ 'required' , 'numeric:' ] ,
                'features'   => [ 'sometimes','string' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
