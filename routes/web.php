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
    Route::group( [ 'prefix' => config( 'sanctum.prefix' , 'sanctum' ) ] , static function () {
        Route::get( '/csrf-cookie' , [ CsrfCookieController::class , 'show' ] )
             ->middleware( [
                 'web' ,
                 InitializeTenancyByDomain::class // Use tenancy initialization middleware of your choice
             ] )->name( 'sanctum.csrf-cookie' );
    } );

    require __DIR__ . '/auth.php';
