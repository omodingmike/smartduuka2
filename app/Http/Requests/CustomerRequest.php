<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class CustomerRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules()
        {
            // Get the customer ID from the route for the unique ignore rule
            // Assuming your route is /customers/{customer}
            $customerId = $this->route( 'customer' ) ? $this->route( 'customer' )->id : NULL;

            return [
                'name'   => [ 'required' , 'string' , 'max:190' ] ,
                'phone'  => [
                    'required' ,
                    'string' ,
                    'max:190' ,
                    // Added unique check for phone while ignoring the current user
                    Rule::unique( 'users' , 'phone' )->ignore( $customerId )
                ] ,
                'phone2' => [ 'nullable' , 'string' , 'max:190' ] ,
                'type'   => [ 'required' , 'string' , 'max:190' ] , // Changed to required to prevent null in state
                'notes'  => [ 'nullable' , 'string' , 'max:255' ] ,
                'email'  => [
                    'nullable' ,
                    'email' ,
                    'max:190' ,
                    // Use the $customerId to ignore the current record during updates
                    Rule::unique( 'users' , 'email' )->ignore( $customerId )
                ] ,
                'status' => [ 'sometimes' , 'numeric' , 'max:24' ] ,
            ];
        }
    }
