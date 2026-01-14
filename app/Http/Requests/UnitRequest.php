<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UnitRequest extends FormRequest
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
                'name'              => [
                    'required' ,
                    'string' ,
                    'max:190' ,
                    Rule::unique( "units" , "name" )->ignore( $this->route( 'unit.id' ) )
                ] ,
                'status'            => [ 'required' , 'numeric' ] ,
                'short_name'        => [ 'required' , 'string' ] ,
                'conversion_factor' => [ 'required' , 'numeric:' ] ,
                'base_unit_id'      => [ 'sometimes' , 'numeric:' ] ,
            ];
        }
    }
