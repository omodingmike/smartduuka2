<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class TenantSubscriptionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'duration' => [ 'required' , 'integer' ] ,
                'plan'     => [ 'required' , 'integer' ] ,
                'setup'    => [ 'required' , 'decimal:2' ] ,
                'amount'   => [ 'required' , 'decimal:2' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
