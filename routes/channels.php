<?php

    use Illuminate\Support\Facades\Broadcast;

    Broadcast::channel( 'business.{identifier}' , function ($user , $identifier) {
        $tenant = tenant();
        if ( $tenant ) return TRUE;
        return FALSE;
    } );