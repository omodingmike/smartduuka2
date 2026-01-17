<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CustomerAddressRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize() : bool
        {
            return TRUE;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules() : array
        {
            return [
                'type'        => [ 'required' , 'string' , 'max:190' ] ,
                'city'        => [ 'required' , 'string' , 'max:190' ] ,
                'addressLine' => [ 'required' , 'string' , 'max:190' ] ,
                'isDefault'   => [ 'required' , 'string' , 'max:190' ] ,

//                'email'        => [ 'nullable' , 'string' , 'max:190' ] ,
//                'country_code' => [ 'required' , 'string' , 'max:28' ] ,
//                'phone'        => [ 'required' , 'string' , 'max:20' ] ,
//                'country'      => [ 'required' , 'numeric' ] ,
//                'state'        => [ 'nullable' , 'numeric' ] ,
//                'country_id'   => [ 'required' , 'numeric' ] ,
//                'state_id'     => [ 'required' , 'numeric' ] ,
//                'city_id'      => [ 'required' , 'numeric' ] ,
//                'city'         => [ 'nullable' , 'numeric'  ] ,
//                'zip_code'     => [ 'nullable' , 'string' ] ,
//                'address'      => [ 'required' , 'string' , 'max:500' ] ,
            ];
        }
    }
