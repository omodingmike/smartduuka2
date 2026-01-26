<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class SiteRequest extends FormRequest
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
                'site_date_format'               => [ 'sometimes' , 'string' , 'max:190' ] ,
                'site_time_format'               => [ 'sometimes' , 'string' , 'max:190' ] ,
                'site_default_timezone'          => [ 'sometimes' , 'string' , 'max:190' ] ,
                'site_default_currency'          => [ 'sometimes' , 'numeric' ] ,
                'site_digit_after_decimal_point' => [ 'sometimes' , 'numeric' , 'max:6' ] ,
                'site_copyright'                 => [ 'sometimes' , 'string' , 'max:190' ] ,
                'site_default_language'          => [ 'sometimes' , 'numeric' ] ,
                'site_default_branch'            => [ 'sometimes' , 'numeric' ] ,
                'site_google_map_key'            => [ 'sometimes' , 'string' ] ,
                'site_digits_after_decimal'      => [ 'sometimes' , 'numeric:' ] ,
                'site_currency_position'         => [ 'sometimes' , 'numeric' ] ,
                'site_email_verification'        => [ 'sometimes' , 'numeric' ] ,
                'site_phone_verification'        => [ 'sometimes' , 'numeric' ] ,
                'site_online_payment_gateway'    => [ 'sometimes' , 'numeric' ] ,
//                'site_language_switch'                       => [ 'required' , 'numeric' ] ,
//                'site_app_debug'                             => [ 'required' , 'numeric' ] ,
//                'site_auto_update'                           => [ 'required' , 'numeric' ] ,
//                'site_sell_from_warehouse'                   => [ 'required' , 'numeric' ] ,
//                'site_android_app_link'                      => [ 'nullable' , 'string' , 'max:190' ] ,
//                'site_ios_app_link'                          => [ 'nullable' , 'string' , 'max:190' ] ,
//                'site_online_payment_gateway'                => [ 'required' , 'numeric' ] ,
//                'site_cash_on_delivery'                      => [ 'required' , 'numeric' ] ,
//                'site_sell'                                  => [ 'required' , 'numeric' ] ,
//                'site_cart_price_editing'                    => [ 'required' , 'numeric' ] ,
//                'site_non_purchase_product_maximum_quantity' => [ 'required' , 'numeric' ] ,
//                'site_is_return_product_price_add_to_credit' => [ 'required' , 'numeric' ] ,
            ];
        }
    }
