<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class UpdateRoyaltyPackageRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'           => [
                    'required' ,
                    'string' ,
                    'max:255' ,
                    Rule::unique('royalty_packages' , 'name')->ignore($this->route('royaltyPackage')) ,
                ] ,
                'status'         => 'required|numeric' ,
                'description'    => 'sometimes|string' ,
                'minimum_points' => 'required|integer' ,
            ];
        }
    }
