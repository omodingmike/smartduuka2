<?php

    use Illuminate\Support\Facades\Route;
    use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

    foreach ( config( 'tenancy.central_domains' , [] ) as $domain ) {
        Route::get( 'csrf-cookie' , [ CsrfCookieController::class , 'show' ] )
             ->middleware( [
                 'web' ,
             ] )->name( 'csrf-cookie' );

        Route::domain( $domain )->group( function () {
            Route::get( '/' , function () {
                return [ 'Laravel' => app()->version() ];
            } );

            Route::get('/q/{tenant}/{quotation}', function (string $tenant, string $quotation) {
                return redirect("https://{$tenant}.smartduuka.com/share/quotation/{$quotation}");
            });
            Route::get( '/opcache' , function () {
                return response()->json( opcache_get_status( FALSE ) );
            } );
        } );
    }