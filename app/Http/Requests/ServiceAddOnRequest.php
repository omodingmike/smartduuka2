<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ServiceAddOnRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'       => [ 'required' ] ,
                'price'      => [ 'required' ] ,
                'service_id' => [ 'required' , 'exists:services' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
