<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class ProductVariationRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize() : bool
        {
            return TRUE;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules() : array
        {
            return [
                'product_attributes'        => [ 'required' , 'string' ] ,
                'product_attribute_options' => [ 'required' , 'string' ] ,
                'product_id'                => [ 'required' , 'numeric' ] ,
//                'name'                        => [ 'required' , 'string' ] ,
                'sku'                       => [ 'required' , 'string' ] ,
                'barcode'                   => [ 'required' , 'string' ] ,
                'trackStock'                => [ 'sometimes' , 'numeric' ] ,
                'retail_pricing'            => [ 'required' , 'string' ] ,
                'wholesale_pricing'         => [ 'sometimes' , 'string' ] ,
                'image'                     => [ 'sometimes' , 'file' ] ,
            ];
        }
    }
