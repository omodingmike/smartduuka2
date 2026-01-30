<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerAddressRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'type'         => [ 'required' , 'string' , 'max:190' ] ,
                'city'         => [ 'required' , 'string' , 'max:190' ] ,
                'address_line' => [ 'required' , 'string' , 'max:190' ] ,
                'is_default'   => [ 'required' , 'string' , 'max:190' ] ,
            ];
        }
    }
