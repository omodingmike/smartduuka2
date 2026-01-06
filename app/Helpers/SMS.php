<?php

    namespace App\Helpers;

    use AfricasTalking\SDK\AfricasTalking;

    trait SMS
    {
        public function send(array $data) : bool
        {
            $africasTalking = new AfricasTalking(
                username: config( 'at.username' ) ,
                apiKey: config( 'at.api_key' )
            );

            $phone_number = $data[ 'to' ];
            $message      = $data[ 'message' ];
            $shortcode    = 'ATTech';

            // --- NORMALIZE PHONE NUMBER ---
            // 1. Remove any non-numeric characters (except maybe the leading plus)
            $clean_phone = preg_replace( '/[^0-9]/' , '' , $phone_number );

            // 2. Handle different formats to ensure it starts with +256
            if ( str_starts_with( $clean_phone , '256' ) ) {
                $phone_number = '+' . $clean_phone;
            }
            elseif ( str_starts_with( $clean_phone , '0' ) ) {
                $phone_number = '+256' . substr( $clean_phone , 1 );
            }
            elseif ( ! str_starts_with( $phone_number , '+256' ) ) {
                // If it's just a 9-digit number like 772...
                $phone_number = '+256' . $clean_phone;
            }
            // ------------------------------

            $sms = $africasTalking->sms();

            // Logic for specialized handling based on carrier prefixes (MTN/Airtel)
            if ( preg_match( '/^\+256(78|77|76|75|70)/' , $phone_number ) ) {
                $response = $sms->send( [
                    'to'      => $phone_number ,
                    'message' => $message ,
                ] );
            }
            else {
                $response = $sms->send( [
                    'to'      => $phone_number ,
                    'message' => $message ,
                    'from'    => $shortcode
                ] );
            }

            if ( isset( $response[ 'status' ] ) ) {
                $response_message = strtolower( data_get( $response , 'data.SMSMessageData.Message' ) );
                // Added a check to ensure the path exists before doing string manipulation
                $smsData = data_get( $response , 'data.SMSMessageData.Recipients.0.status' );

                if ( $smsData === 'Success' || $smsData === 'Sent' ) {
                    return TRUE;
                }
            }
            return FALSE;
        }
    }