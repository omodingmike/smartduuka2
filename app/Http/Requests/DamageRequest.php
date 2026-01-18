<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class DamageRequest extends FormRequest
    {
        /**
         * Determine if the user is authorized to make this request.
         */
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'date'         => [ 'required' , 'date' ] ,
                'product_id'   => [ 'required' , 'numeric' ] ,
                'variation_id' => [ 'sometimes' , 'numeric' ] ,
                'quantity'     => [ 'required' , 'numeric' ] ,
                'reason'       => [ 'required' , 'string' ] ,
                'notes'        => [ 'sometimes' , 'string' ] ,
                'image'        => [ 'nullable' , 'file' , 'mimes:jpg,jpeg,png,pdf' , 'max:2048' ] ,

//            'discount' => ['nullable', 'numeric'],
//            'tax'      => ['required', 'numeric'],
//            'total'    => ['required', 'numeric'],
//            'note'     => ['nullable', 'string', 'max:1000'],
//            'products' => ['required', 'json']
            ];
        }

        public function withValidator1($validator) : void
        {
            $validator->after( function ($validator) {
                $status   = FALSE;
                $message  = '';
                $products = json_decode( $this->products , TRUE );
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

                if ( $status ) {
                    $validator->errors()->add( 'global' , $message );
                }
            } );
        }
    }
