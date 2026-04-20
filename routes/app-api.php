<?php

    declare( strict_types = 1 );

    use App\Http\Controllers\Auth\Apps\AuthenticatedSessionController;
    use App\Http\Controllers\Auth\CentralLoginController;
    use App\Http\Controllers\BusinessController;
    use Illuminate\Support\Facades\Route;
    use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

    Route::middleware( [ 'api' , InitializeTenancyByRequestData::class , 'auth:sanctum' ] )
         ->prefix( 'app' )->group( function () {
            Route::get( 'user' , [ CentralLoginController::class , 'me' ] );
            Route::apiResource( 'businesses' , BusinessController::class )->names( 'app.businesses' );;
            Route::post( 'logout' , [ AuthenticatedSessionController::class , 'destroy' ] );
        } );
