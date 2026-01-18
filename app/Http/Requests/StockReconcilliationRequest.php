<?php

    namespace App\Http\Requests;

    use App\Enums\Enabled;
    use Illuminate\Foundation\Http\FormRequest;
    use Smartisan\Settings\Facades\Settings;

    class StockReconcilliationRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'warehouse_id' => [ 'required' ] ,
                'products'     => [ 'required' , 'string' ] ,
                'date'         => [ 'required' , 'date' ]
            ];
        }

        public function withValidator1($validator)
        {
            $validator->after( function ($validator) {
                $status           = FALSE;
                $message          = '';
                $module_warehouse = Settings::group( 'module' )->get( 'module_warehouse' );
                $products         = json_decode( $this->products , TRUE );
                if ( is_array( $products ) && count( $products ) ) {
                    foreach ( $products as $product ) {
                        if ( $product[ 'quantity' ] < 1 || ! is_numeric( $product[ 'quantity' ] ) || ! is_int( (int) $product[ 'quantity' ] ) ) {
                            $status  = TRUE;
                            $message = trans( 'all.message.product_quantity_invalid' );
                        }
                        else if ( ! is_numeric( $product[ 'price' ] ) || ! is_double( (float) $product[ 'price' ] ) || $product[ 'price' ] == 0 || $product[ 'price' ] < 0 ) {
                            $status  = TRUE;
                            $message = trans( 'all.message.product_price_invalid' );
                        }
                        else if ( ! is_numeric( $product[ 'total' ] ) || ! is_double( (float) $product[ 'total' ] ) ) {
                            $status  = TRUE;
                            $message = trans( 'all.message.product_price_total_invalid' );
                        }
                    }
                }
                else {
                    $validator->errors()->add( 'products' , trans( 'all.message.product_invalid' ) );
                }
                if ( $module_warehouse == Enabled::YES && $this->warehouse_id == 'null' ) {
                    $validator->errors()->add( 'warehouse_id' , "The warehouse field is required." );
                }

                if ( $status ) {
                    $validator->errors()->add( 'global' , $message );
                }
            } );
        }
    }
