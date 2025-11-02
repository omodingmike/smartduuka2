<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class CommissionTargetRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'commission_id'        => [ 'required' , 'exists:commissions' ] ,
                'user_id'              => [ 'required' , 'exists:users' ] ,
                'role_id'              => [ 'required' , 'exists:roles' ] ,
                'product_id'           => [ 'required' , 'exists:products' ] ,
                'product_variation_id' => [ 'required' , 'exists:product_variations' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
