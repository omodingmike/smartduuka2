<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class UpdateProductionSetupRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'name'        => 'required|string' ,
                'product_id'     => 'required|integer' ,
                'ingredients' => 'required|array' ,
            ];
        }
    }
