<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreWarehouseRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'     => [ 'required' , 'string' , 'max:255' ] ,
                'status'   => [ 'required' , 'numeric:' ] ,
                'phone'    => [ 'sometimes' , 'unique:warehouses' , 'max:255' ] ,
                'email'    => [ 'sometimes' , 'email' , 'unique:warehouses' , 'max:255' ] ,
                'location' => [ 'sometimes' , 'string' , 'max:255' ] ,
                'manager'  => [ 'sometimes' , 'string' , 'max:255' ] ,
                'capacity' => [ 'sometimes' , 'string' , 'max:255' ] ,
            ];
        }
    }
