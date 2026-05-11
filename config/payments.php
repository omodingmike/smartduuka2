<?php

    return [

        /*
        |--------------------------------------------------------------------------
        | Default Payment Gateway
        |--------------------------------------------------------------------------
        | Supported: "yo_uganda", "iotec"
        */
        'default'          => env( 'PAYMENT_GATEWAY' , 'iotec' ) ,

        /*
        |--------------------------------------------------------------------------
        | Local tunnel URL (used in isLocal() mode for webhooks)
        |--------------------------------------------------------------------------
        */
        'local_tunnel_url' => env( 'PAYMENT_LOCAL_TUNNEL_URL' , 'https://hope-sql-conceptual-therapist.trycloudflare.com' ) ,

        /*
        |--------------------------------------------------------------------------
        | Gateway credentials
        |--------------------------------------------------------------------------
        */
        'yo'               => [
            'username' => env( 'YO_USERNAME' ) ,
            'password' => env( 'YO_PASSWORD' ) ,
        ] ,
        'iotec'            => [
            'iotec_wallet_id' => env( 'IO_TEC_WALLET_ID' ) ,
            'iotec_client_id' => env( 'IO_TEC_CLIENT_ID' ) ,
            'iotec_secrete'   => env( 'IO_TEC_SECRET' ) ,
        ]
    ];