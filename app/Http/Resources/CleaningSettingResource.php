<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class CleaningSettingResource extends JsonResource
    {

        public $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        public function toArray($request) : array
        {
            info( $this->info );
            $keys = [
                'show_customer_phone' ,
                'show_service_method' ,
                'show_item_list' ,
                'show_order_date' ,
                'show_business_name' ,
                'show_business_phone' ,
                'show_business_address' ,
                'enable_online_bookings' ,
                'show_service_images' ,
                'enable_delivery_service' ,
            ];
            $data = [];

            foreach ( $keys as $key ) {
                $data[ $key ] = filter_var( $this->info[ $key ] ?? FALSE , FILTER_VALIDATE_BOOLEAN );
            }
            return [
                "order_prefix"            => $this->info[ 'order_prefix' ] ?? 'SDCC-' ,
                "welcome_message"         => $this->info[ 'welcome_message' ] ?? '' ,
                "free_delivery_threshold" => $this->info[ 'free_delivery_threshold' ] ?? '' ,
                "delivery_fee"            => $this->info[ 'delivery_fee' ] ?? '' ,
                ...$data
            ];
        }
    }
