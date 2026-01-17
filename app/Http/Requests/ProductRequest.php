<?php

    namespace App\Http\Requests;

    use App\Models\ProductVariation;
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
                'name'                       => [
                    'required' ,
                    'string' ,
                    'max:190' ,
                    Rule::unique( 'products' , 'name' )->whereNull( 'deleted_at' )->ignore( $this->route( 'product.id' ) )
                ] ,
                'sku'                        => [
                    'required' ,
                    Rule::unique( 'products' , 'sku' )->whereNull( 'deleted_at' )->ignore( $this->route( 'product.id' ) )
                ] ,
                'barcode'                    => [ 'required' , 'numeric' , 'not_in:0' ] ,
                'type'                       => [ 'required' , 'numeric' , 'not_in:0' ] ,
                'trackStock'                 => [ 'sometimes' , 'numeric' , 'not_in:0' ] ,
                'product_category_id'        => [ 'required' , 'numeric' , 'not_in:0' ] ,
                'product_brand_id'           => [ 'nullable' , 'numeric' , 'max_digits:10' ] ,
                'weight'                     => [ 'nullable' , 'string' , 'max:100' ] ,
                'weight_unit_id'             => [ 'nullable' , 'string' , 'max:100' ] ,
                'tags'                       => [ 'nullable' , 'string' ] ,
                'unit_pricing'               => [ 'nullable' , 'json' ] ,
                'returnable'                 => [ 'nullable' , 'string' ] ,
                'description'                => [ 'nullable' , 'string' , 'max:5000' ] ,
                'image'                      => 'sometimes|file' ,
                'status'                     => [ 'required' , 'numeric' , 'max:24' ] ,
                'can_purchasable'            => [ 'required' , 'numeric' , 'max:24' ] ,
                'stock_out'                  => [ 'required' , 'numeric' , 'max:24' ] ,
                'stock'                      => "required_if:trackStock,1" ,
                'low_stock_quantity_warning' => "required_if:trackStock,1" ,
//                'buying_price'               => [ 'required' , new IniAmount() ] ,
//                'selling_prices'              => [ 'sometimes' , new IniAmount() ] ,
            ];
        }

        public function attributes() : array
        {
            return [
                'product_category_id' => strtolower( trans( 'all.label.product_category_id' ) ) ,
                'product_brand_id'    => strtolower( trans( 'all.label.product_brand_id' ) ) ,
                'barcode_id'          => strtolower( trans( 'all.label.barcode_id' ) ) ,
                'unit_id'             => strtolower( trans( 'all.label.unit_id' ) ) ,
                'tax_id'              => strtolower( trans( 'all.label.tax_id' ) ) ,
            ];
        }

        public function withValidator($validator) : void
        {
            $validator->after( function ($validator) {
                $sku = ProductVariation::where( 'sku' , $this->sku )->first();
                if ( $sku ) {
                    $validator->getMessageBag()->add( 'sku' , trans( 'all.message.sku_exist' ) );
                }
            } );
        }
    }
