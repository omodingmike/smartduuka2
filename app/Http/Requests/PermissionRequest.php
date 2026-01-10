<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PermissionRequest extends FormRequest
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
                'permissions'   => [ 'nullable' , 'array' ] ,
                'permissions.*' => [ 'integer' , 'exists:permissions,id' ]
            ];
        }

        protected function prepareForValidation() : void
        {
            $permissions = $this->input( 'permissions' );

            // If it's the JSON string "[1,2,3]" from serializeValue, decode it
            if ( $permissions && is_string( $permissions ) ) {
                $decoded = json_decode( $permissions , TRUE );

                $this->merge( [
                    'permissions' => is_array( $decoded ) ? $decoded : []
                ] );
            }
        }
    }
