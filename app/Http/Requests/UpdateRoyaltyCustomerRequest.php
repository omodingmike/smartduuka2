<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateRoyaltyCustomerRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'dob'             => 'date' ,
                'city'            => 'string' ,
                'name'            => 'string|regex:/^\S+(?:\s+\S+)+$/' ,
                'country'         => 'string' ,
                'email'           => [
                    'required' ,
                    'string' ,
                    Rule::unique('royalty_customers' , 'email')->ignore($this->route('royaltyCustomer')) ,
                ] ,
                'phone'           => [
                    'required' ,
                    'numeric' ,
                    'digits:9' ,
                    Rule::unique('royalty_customers' , 'phone')->ignore($this->route('royaltyCustomer')) ,
                ] ,
                'reward_location' => 'string' ,
                'contact_method'  => 'string' ,
                'info_source'     => 'string' ,
                'package_id'      => 'numeric' ,
                'status'          => 'numeric' ,
                'referer'         => 'nullable|string' ,
            ];
        }
    }
