<?php

    namespace App\Http\Requests;

    use App\Enums\UnitsNature;
    use App\Models\ProductVariation;
    use App\Rules\IniAmount;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class ProductRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize() : bool
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules() : array
        {
            return [
                'name'                       => [
                    'required' ,
                    'string' ,
                    'max:190' ,
                    Rule::unique('products' , 'name')->whereNull('deleted_at')->ignore($this->route('product.id'))
                ] ,
                'product_category_id'        => [ 'required' , 'numeric' , 'not_in:0' ] ,
                'barcode_id'                 => [ 'required' , 'numeric' , 'not_in:0' ] ,
                'sku'                        => [
                    'required' ,
                    'numeric' , 'regex:/^\d{12,13}$/' ,
                    Rule::unique('products' , 'sku')->whereNull('deleted_at')->ignore($this->route('product.id'))
                ] ,
                'buying_price'               => [ 'required' , new IniAmount() ] ,
                'selling_price'              => [ 'sometimes' , new IniAmount() ] ,
                'tax_id[]'                   => [ 'nullable' , 'numeric' , 'max_digits:10' ] ,
                'product_brand_id'           => [ 'nullable' , 'numeric' , 'max_digits:10' ] ,
                'status'                     => [ 'required' , 'numeric' , 'max:24' ] ,
                'units_nature'               => [ 'required' , 'numeric' , 'max:24' ] ,
                'can_purchasable'            => [ 'required' , 'numeric' , 'max:24' ] ,
                'show_stock_out'             => [ 'required' , 'numeric' , 'max:24' ] ,
                'refundable'                 => [ 'required' , 'numeric' , 'max:24' ] ,
                'maximum_purchase_quantity'  => [ 'required' , 'numeric' , 'max_digits:10' ] ,
                'low_stock_quantity_warning' => [ 'required' , 'numeric' , 'max_digits:10' ] ,
                'other_unit_id'              => 'required_if:units_nature,' . UnitsNature::MULTIPLE . '|numeric|not_in:0' ,
                'weight'                     => [ 'nullable' , 'string' , 'max:100' ] ,
                'description'                => [ 'nullable' , 'string' , 'max:5000' ] ,
                'product_retail_prices'      => [ 'nullable' , 'array' ] ,
                'product_wholesale_prices'   => [ 'nullable' , 'array' ] ,
                'tags'                       => [ 'nullable' , 'json' ] ,
                'unit_id'                    => 'required|exists:units,id|not_in:0' ,
                'retail_unit_id'             => 'sometimes|exists:units,id|not_in:0' ,
                'mid_unit_id'                => 'sometimes|exists:units,id|not_in:0' ,
                'top_unit_id'                => 'sometimes|exists:units,id|not_in:0' ,
                'units_per_mid_unit'         => 'sometimes|numeric' ,
                'mid_units_per_top_unit'     => 'sometimes|numeric' ,
                'base_units_per_top_unit'    => 'sometimes|numeric' ,
                'retail_price_per_base_unit' => 'sometimes|numeric' ,
                'mid_unit_wholesale_price'   => 'sometimes|numeric' ,
                'top_unit_wholesale_price'   => 'sometimes|numeric' ,
                'image'   => 'sometimes|file' ,
            ];
        }

        public function attributes() : array
        {
            return [
                'product_category_id' => strtolower(trans('all.label.product_category_id')) ,
                'product_brand_id'    => strtolower(trans('all.label.product_brand_id')) ,
                'barcode_id'          => strtolower(trans('all.label.barcode_id')) ,
                'unit_id'             => strtolower(trans('all.label.unit_id')) ,
                'tax_id'              => strtolower(trans('all.label.tax_id')) ,
            ];
        }

        public function withValidator($validator) : void
        {
            $validator->after(function ($validator) {
                $sku = ProductVariation::where('sku' , $this->sku)->first();
                if ( $sku ) {
                    $validator->getMessageBag()->add('sku' , trans('all.message.sku_exist'));
                }
            });
        }
    }
