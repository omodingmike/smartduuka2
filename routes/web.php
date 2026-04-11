<?php

    use App\Http\Controllers\UserController;
    use Illuminate\Support\Facades\Route;
    use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

    Route::middleware( [ 'auth:sanctum' ] )->get( 'user' , [ UserController::class , 'user' ] );
    foreach ( config( 'tenancy.central_domains' , [] ) as $domain ) {
        Route::get( 'csrf-cookie' , [ CsrfCookieController::class , 'show' ] )
             ->middleware( [
                 'web' ,
             ] )->name( 'csrf-cookie' );

        Route::domain( $domain )->group( function () {
            Route::get( '/' , function () {
                return [ 'Laravel' => app()->version() ];
            } );
            Route::get( '/opcache' , function () {
                return response()->json( opcache_get_status( FALSE ) );
            } );
        } );
    }