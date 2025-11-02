<?php
    return [
        'main_menu' => [
            'message' => 'Welcome' ,
            'options' => [
                '1' => 'Renew Subscription' ,
                '2' => 'Add Subscription' ,
                '3' => 'Upgrade Subscription' ,
            ] ,
        ] ,
        'whatsapp_access_token'    => env('WHATSAPP_API_ACCESS_TOKEN' , '') ,
        'whatsapp_phone_number_id' => env('WHATSAPP_API_PHONE_NUMBER_ID' , '') ,
    ];

