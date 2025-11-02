<?php
    return [
        'quotations'             => env( 'LOCAL' ) == 'true' ,
        'credit'                 => TRUE ,
        'demo'                   => env( 'DEMO' ) ,
        'iotec_wallet_id'        => env( 'IO_TEC_WALLET_ID' ) ,
        'iotec_client_id'        => env( 'IO_TEC_CLIENT_ID' ) ,
        'iotec_secrete'          => env( 'IO_TEC_SECRET' ) ,
        'date_format'            => env( 'DATE_FORMAT' ) ,
        'time_format'            => env( 'TIME_FORMAT' ) ,
        'currency'               => env( 'CURRENCY' ) ,
        'non_purchase_quantity'  => env( 'NON_PURCHASE_QUANTITY' ) ,
        'currency_position'      => env( 'CURRENCY_POSITION' ) ,
        'currency_symbol'        => env( 'CURRENCY_SYMBOL' ) ,
        'currency_decimal_point' => env( 'CURRENCY_DECIMAL_POINT' ) ,
        'fcm_secret_key'         => env( 'FCM_SECRET_KEY' ) ,
    ];
