<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreRoyaltyPackageBenefitRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'        => 'required|string|max:255,unique:royalty_package_benefits,name' ,
                'status'      => 'required|numeric' ,
                'description' => 'nullable|string' ,
                'discount'    => 'nullable|numeric' ,
            ];
        }
    }
