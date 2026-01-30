<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class SupplierRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'    => [ 'required' , 'string' , 'max:190' ] ,
                'email'   => [ 'nullable' , 'email' , 'max:190' , Rule::unique( "suppliers" , "email" )->ignore( $this->route( 'supplier.id' ) ) ] ,
                'phone'   => [ 'nullable' , 'string' , 'max:20' , Rule::unique( "suppliers" , "phone" )->ignore( $this->route( 'supplier.id' ) ) ] ,
                'address' => [ 'nullable' , 'string' , 'max:500' ] ,
                'tin'     => [ 'nullable' , 'string' , 'max:500' ] ,
                'status'  => [ 'required' , 'numeric:' ] ,
            ];
        }
    }