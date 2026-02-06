<?php

    use Illuminate\Support\Facades\Route;
    use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
    use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

    Route::get( '/' , function () {
        return [ 'Laravel' => app()->version() ];
    } );
    Route::get( '/opcache' , function () {
        return response()->json( opcache_get_status( FALSE ) );
    } );

    require __DIR__ . '/auth.php';
