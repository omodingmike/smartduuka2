<?php

    namespace App\Http\Requests;

    use App\Enums\Enabled;
    use Illuminate\Foundation\Http\FormRequest;
    use Smartisan\Settings\Facades\Settings;

    class PurchaseRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'supplier_id'   => [ 'sometimes' , 'numeric' , 'not_in:0' , 'not_in:null' ] ,
                'date'          => [ 'required' , 'string' ] ,
                'status'        => [ 'sometimes' , 'numeric' , 'not_in:0' , 'not_in:null' ] ,
                'total'         => [ 'sometimes' , 'numeric' , 'numeric' ] ,
                'file'          => [ 'nullable' , 'file' , 'mimes:jpg,jpeg,png,pdf' , 'max:2048' ] ,
                'note'          => [ 'nullable' , 'string' , 'max:1000' ] ,
                'otherQuantity' => [ 'sometimes' , 'numeric' ] ,
                'products'      => [ 'required' , 'json' ]
            ];
        }

        public function withValidator($validator)
        {
            $validator->after(function ($validator) {
                $status           = false;
                $message          = '';
                $module_warehouse = Settings::group('module')->get('module_warehouse');
                $products         = json_decode($this->products , true);
                if ( is_array($products) && count($products) ) {
                    foreach ( $products as $product ) {
                        if ( $product['quantity'] < 1 || ! is_numeric($product['quantity']) || ! is_int((int) $product['quantity']) ) {
                            $status  = true;
                            $message = trans('all.message.product_quantity_invalid');
                        } else if ( ! is_numeric($product['price']) || ! is_double((float) $product['price']) || $product['price'] == 0 || $product['price'] < 0 ) {
                            $status  = true;
                            $message = trans('all.message.product_price_invalid');
                        } else if ( ! is_numeric($product['total']) || ! is_double((float) $product['total']) ) {
                            $status  = true;
                            $message = trans('all.message.product_price_total_invalid');
                        }
                    }
                } else {
                    $validator->errors()->add('products' , trans('all.message.product_invalid'));
                }
                if ( $module_warehouse == Enabled::YES && $this->warehouse_id == 'null' ) {
                    $validator->errors()->add('warehouse_id' , "The warehouse field is required.");
                }

                if ( $status ) {
                    $validator->errors()->add('global' , $message);
                }
            });
        }
    }
