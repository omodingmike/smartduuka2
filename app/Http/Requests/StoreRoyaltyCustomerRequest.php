<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Support\Carbon;

    class StoreRoyaltyCustomerRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'dob'             => 'date|before_or_equal:' . Carbon::now()->subYears(18)->format('Y-m-d') ,
                'city'            => 'string' ,
                'name'            => 'string|regex:/^\S+(?:\s+\S+)+$/' ,
                'country'         => 'string' ,
                'email'           => 'email|unique:royalty_customers' ,
                'phone'           => 'numeric|unique:royalty_customers|digits:9' ,
                'reward_location' => 'string' ,
                'contact_method'  => 'string' ,
                'info_source'     => 'string' ,
                'package_id'      => 'numeric' ,
                'status'          => 'numeric' ,
                'referer'         => 'nullable|string' ,
            ];
        }
    }
