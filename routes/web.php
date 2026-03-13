<?php

    use Illuminate\Support\Facades\Route;

    foreach ( config( 'tenancy.central_domains' , [] ) as $domain ) {
        Route::domain( $domain )->group( function () {

            Route::get( '/' , function () {
                return [ 'Laravel' => app()->version() ];
            } );
            Route::get( '/opcache' , function () {
                return response()->json( opcache_get_status( FALSE ) );
            } );

            require __DIR__ . '/auth.php';

        } );
    }