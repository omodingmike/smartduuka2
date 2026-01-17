<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateWarehouseRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'         => [ 'required' , 'string' , 'max:255' ] ,
                'phone'        => [ 'sometimes' , 'max:255' , Rule::unique( 'warehouses' )->ignore( $this->warehouse ) ] ,
                'email'        => [ 'sometimes' , 'email' , 'max:255' , Rule::unique( 'warehouses' )->ignore( $this->warehouse ) ] ,
                'location'     => [ 'sometimes' , 'string' , 'max:255' ] ,
                'country_code' => [ 'sometimes' , 'string' , 'max:255' ] ,
                'status'       => [ 'required' , 'numeric:' ] ,
                'manager'      => [ 'sometimes' , 'string' , 'max:255' ] ,
                'capacity'     => [ 'sometimes' , 'string' , 'max:255' ] ,
            ];
        }
    }
