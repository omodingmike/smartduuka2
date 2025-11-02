<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class StoreIngredientStockRequest extends FormRequest
    {

        public function authorize() : bool
        {
            return true;
        }

        protected function prepareForValidation() : void
        {
            if ( $this->has('products') ) {
                $this->merge([
                    'products' => json_decode($this->products , true) ,
                ]);
            }
        }

        public function rules() : array
        {
            return [
                'products' => [ 'required' , 'array' ]
            ];
        }

        public function messages() : array
        {
            return [
                'products' => 'Select Raw materials' ,
            ];
        }
    }
