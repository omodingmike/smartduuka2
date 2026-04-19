<?php

    declare( strict_types = 1 );

    use App\Http\Controllers\Auth\Apps\AuthenticatedSessionController;
    use App\Http\Controllers\Auth\CentralLoginController;
    use App\Http\Controllers\Cashflow\ActivityLogController;
    use App\Http\Controllers\Cashflow\CurrencyController;
    use App\Http\Controllers\Cashflow\DashboardController;
    use App\Http\Controllers\Cashflow\EntityController;
    use App\Http\Controllers\Cashflow\MotherAccountController;
    use App\Http\Controllers\Cashflow\SettingsController;
    use App\Http\Controllers\Cashflow\SubAccountController;
    use App\Http\Controllers\Cashflow\TransactionCategoryController;
    use App\Http\Controllers\Cashflow\TransactionController;
    use App\Http\Controllers\TenantController;
    use Illuminate\Support\Facades\Route;
    use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

    Route::middleware( [ 'api' , InitializeTenancyByRequestData::class , 'auth:sanctum' ] )
         ->prefix( 'app' )->group( function () {
            Route::get( 'user' , [ CentralLoginController::class , 'me' ] );
            Route::apiResource( 'tenants' , TenantController::class );
            Route::post( 'logout' , [ AuthenticatedSessionController::class , 'destroy' ] );
        } );
