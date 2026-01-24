<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class CustomerRequest extends FormRequest
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
        public function rules()
        {
            return [
                'name'   => [ 'required' , 'string' , 'max:190' ] ,
                'phone'  => [ 'required' , 'string' , 'max:190' ] ,
                'phone2' => [ 'sometimes' , 'string' , 'max:190' ] ,
                'type'   => [ 'sometimes' , 'string' , 'max:190' ] ,
                'notes'  => [ 'sometimes' , 'string' , 'max:255' ] ,
                'email'  => [
                    'sometimes' ,
                    'email' ,
                    'max:190' ,
                    Rule::unique( "users" , "email" )->ignore( $this->route( 'customer.id' ) )
                ] ,
                'status'       => [ 'sometimes' , 'numeric' , 'max:24' ] ,
            ];
        }
    }
