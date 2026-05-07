<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class TenantSubscriptionRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'phone'            => [ 'required' , 'string' ] ,
                'tenant'           => [ 'required' , 'string' ] ,
                'amount'           => [ 'required' , 'numeric:' ] ,
                'billingCycle'     => [ 'required' , 'numeric:' ] ,
                'subscriptionPlan' => [ 'required' , 'numeric:' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }
    }
