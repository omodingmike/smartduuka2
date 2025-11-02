<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class CommissionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'                => [ 'required' , 'string' , 'max:255' ] ,
                'commission_type'     => [ 'required' , 'integer' ] ,
                'commission_value'    => [ 'required' , 'numeric' ] ,
                'applies_to'          => [ 'required' , Rule::in( [ 'user' , 'role' , 'users' ] ) ] ,
                'applies_to_products' => [ 'required' , Rule::in( [ 'all_products' , 'specific_products' ] ) ] ,
                'products'            => [ 'nullable' ] ,
                'user_id'             => [ 'nullable' , 'integer' , 'exists:users,id' ] ,
                'role_id'             => [ 'nullable' , 'integer' , 'exists:roles,id' ] ,
            ];
        }

        public function withValidator($validator)
        {
            $validator->after( function ($validator) {
                $appliesTo         = $this->input( 'applies_to' );
                $appliesToProducts = $this->input( 'applies_to_products' );

                // Enforce user/role ID rules
                if ( $appliesTo === 'user' && ! $this->filled( 'user_id' ) ) {
                    $validator->errors()->add( 'user_id' , 'The user_id field is required when applies_to is user.' );
                }

                if ( $appliesTo === 'role' && ! $this->filled( 'role_id' ) ) {
                    $validator->errors()->add( 'role_id' , 'The role_id field is required when applies_to is role.' );
                }

                // Enforce products rule
                if ( $appliesToProducts === 'specific_products' && ! $this->filled( 'products' ) ) {
                    $validator->errors()->add( 'products' , 'Products are required when applies_to_products is specific_products.' );
                }
            } );
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
