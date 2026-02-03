<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class SiteResource extends JsonResource
    {
        public $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        public function toArray($request) : array
        {
            return [
                'site_date_format'               => $this->info[ 'site_date_format' ] ?? 'Y-m-d' ,
                'site_time_format'               => $this->info[ 'site_time_format' ] ?? 'H:i' ,
                'site_default_timezone'          => (int) $this->info[ 'site_default_timezone' ] ?? 'UTC' ,
                'site_default_currency'          => (int) $this->info[ 'site_default_currency' ] ?? 'UGX' ,
                'site_default_branch'            => (int) $this->info[ 'site_default_branch' ] ?? 0 ,
                'site_copyright'                 => $this->info[ 'site_copyright' ] ?? '' ,
                'site_digit_after_decimal_point' => $this->info[ 'site_digit_after_decimal_point' ] ?? 2 ,
                'site_default_language'          => (int) $this->info[ 'site_default_language' ] ?? 'en' ,
                'site_google_map_key'            => $this->info[ 'site_google_map_key' ] ?? '' ,
                'site_currency_position'         => (int) $this->info[ 'site_currency_position' ] ?? '' ,
                'site_email_verification'        => (int) $this->info[ 'site_email_verification' ] ?? 0 ,
                'site_phone_verification'        => (int) $this->info[ 'site_phone_verification' ] ?? 0 ,
                'site_online_payment_gateway'    => (int) $this->info[ 'site_online_payment_gateway' ] ?? 0 ,
                'currency'                       => currencySymbol() ,

//                'site_default_currency_symbol'               => $this->info[ 'site_default_currency_symbol' ] ?? 'UGX' ,
//                'site_currency_position'                     => $this->info[ 'site_currency_position' ] ?? 5 ,
//                'site_language_switch'                       => $this->info[ 'site_language_switch' ] ?? Ask::NO ,
//                'site_app_debug'                             => $this->info[ 'site_app_debug' ] ?? Ask::NO ,
//                'site_auto_update'                           => $this->info[ 'site_auto_update' ] ?? Ask::NO ,
//                'site_sell_from_warehouse'                   => $this->info[ 'site_sell_from_warehouse' ] ?? Ask::NO ,
//                'site_android_app_link'                      => $this->info[ 'site_android_app_link' ] ?? '' ,
//                'site_ios_app_link'                          => $this->info[ 'site_ios_app_link' ] ?? '' ,
//                'site_cash_on_delivery'                      => $this->info[ 'site_cash_on_delivery' ] ?? Ask::NO ,
//                'site_sell'                                  => $this->info[ 'site_sell' ] ?? Ask::YES ,
//                'site_cart_price_editing'                    => $this->info[ 'site_cart_price_editing' ] ?? Ask::NO ,
//                'module_warehouse'                           => $this->info[ 'module_warehouse' ] ?? Ask::NO ,
//                'site_non_purchase_product_maximum_quantity' => $this->info[ 'site_non_purchase_product_maximum_quantity' ] ?? 0 ,
//                'site_is_return_product_price_add_to_credit' => $this->info[ 'site_is_return_product_price_add_to_credit' ] ?? Ask::NO ,
            ];
        }
    }