<?php

    namespace App\Http\Requests;

    // Ensure this matches your actual Enum namespace
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class BranchRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            $branchId = $this->route( 'branch' ) instanceof Model
                ? $this->route( 'branch' )->id
                : $this->route( 'branch' );

            return [
                'name'     => [
                    'required' ,
                    'string' ,
                    'max:190' ,
                    Rule::unique( 'branches' , 'name' )->ignore( $branchId )
                ] ,
                'code'     => [
                    'required' ,
                    'string' ,
                    'max:50' ,
                    Rule::unique( 'branches' , 'code' )->ignore( $branchId )
                ] ,
                'location' => [ 'required' , 'string' , 'max:500' ] ,
                'manager'  => [ 'required' , 'string' , 'max:190' ] ,
                'phone'    => [ 'required' , 'string' , 'max:20' ] ,
                'email'    => [ 'nullable' , 'email' , 'max:190' ] ,
                'status'   => [ 'nullable' , 'numeric:' , 'max:190' ] ,
            ];
        }
    }