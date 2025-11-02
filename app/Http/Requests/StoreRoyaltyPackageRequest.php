<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreRoyaltyPackageRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'           => 'required|string|max:255,unique:royalty_packages,name' ,
                'status'         => 'required|numeric' ,
                'description'    => 'nullable|string' ,
                'minimum_points' => 'required|integer' ,
            ];
        }
    }
