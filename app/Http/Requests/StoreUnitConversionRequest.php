<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreUnitConversionRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'base_unit_id'    => 'required|integer|exists:units,id' ,
                'other_unit_id'   => 'required|integer|exists:units,id' ,
                'conversion_rate' => 'required' ,
            ];
        }
    }
