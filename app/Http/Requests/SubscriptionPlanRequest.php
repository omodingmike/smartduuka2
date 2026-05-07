<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class SubscriptionPlanRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'        => [ 'required' ] ,
                'description' => [ 'required' ] ,
                'features'    => [ 'required' ] ,
                'base_amount' => [ 'required' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
