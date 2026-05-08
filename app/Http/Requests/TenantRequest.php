<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rules\Password;

    class TenantRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'businessName'       => [ 'required' , 'string' , 'max:255' ] ,
                'tenant'             => [ 'required' , 'string' , 'max:255' , 'unique:tenants,id' ] ,
                'businessEmail'      => [ 'required' , 'email' , 'max:255'  ] ,
                'businessPhone'      => [ 'required' , 'string' , 'max:255' ] ,
                'mobileMoneyNumber'  => [ 'required' , 'string' , 'max:255' ] ,
                'businessAddress'    => [ 'required' , 'string' , 'max:255' ] ,
                'adminName'          => [ 'required' , 'string' , 'max:255' ] ,
                'adminEmail'         => [ 'required' , 'email' , 'max:255' , 'unique:users,email' ] ,
                'adminPassword'      => [ 'required' , 'string' , Password::defaults() ] ,
                'adminPin'           => [ 'required' , 'numeric' , 'digits:5' ] ,
                'paymentMethod'      => [ 'required' , 'string' ] ,
                'subscriptionPlanId' => [ 'required' , 'integer' , 'exists:subscription_plans,id' ] ,
                'billingCycleId'     => [ 'required' , 'integer' , 'exists:billing_cycles,id' ] ,
                'amountPaid'         => [ 'required' , 'numeric' ] ,
            ];
        }
    }
