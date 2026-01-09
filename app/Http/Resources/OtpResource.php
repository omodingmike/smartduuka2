<?php

    namespace App\Http\Resources;


    use Illuminate\Http\Resources\Json\JsonResource;

    class OtpResource extends JsonResource
    {

        public array $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'delivery_method' => $this->info[ 'delivery_method' ] ?? '' ,
                'otp_digit_limit' => max( 1 , (int) ( $this->info[ 'otp_digit_limit' ] ?? 4 ) ) ,
                'otp_expire_time' => min( 60 , max( 1 , (int) ( $this->info[ 'otp_expire_time' ] ?? 1 ) ) ) ,
                'max_attempts'    => min( 60 , max( 1 , (int) ( $this->info[ 'max_attempts' ] ?? 1 ) ) ) ,
            ];

        }
    }
