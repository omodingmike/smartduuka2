<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreProductionSetupRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'         => 'required|string' ,
                'product_id'      => 'required' ,
                'ingredients'  => 'required|array' ,
                'overall_cost' => 'required|numeric' ,
            ];
        }

    }
