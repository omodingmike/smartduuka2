<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateRoyaltyPackageBenefitRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'        => [
                    'required' ,
                    'string' ,
                    'max:255' ,
                    Rule::unique('royalty_package_benefits' , 'name')->ignore($this->route('royaltyBenefit')) ,
                ] ,
                'status'      => 'required|numeric' ,
                'description' => 'nullable|string' ,
                'discount'    => 'nullable|numeric' ,
            ];
        }
    }
