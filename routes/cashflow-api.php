<?php

    declare( strict_types = 1 );

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
    use Illuminate\Support\Facades\Route;
    use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

    Route::middleware( [ 'api' , InitializeTenancyByRequestData::class , 'auth:sanctum' ] )->prefix( 'cashflow' )->group( function () {
        Route::get( 'me' , [ CentralLoginController::class , 'me' ] );
        Route::get( 'user' , [ CentralLoginController::class , 'me' ] );
        Route::apiResource( 'motherAccounts' , MotherAccountController::class )->except( 'destroy' );
        Route::apiResource( 'subAccounts' , SubAccountController::class )->except( 'destroy' );
        Route::delete( 'subAccounts' , [ SubAccountController::class , 'destroy' ] );
        Route::apiResource( 'currencies' , CurrencyController::class )->except( 'destroy' );
        Route::delete( 'currencies' , [ CurrencyController::class , 'destroy' ] );
        Route::apiResource( 'entities' , EntityController::class )->except( 'destroy' );
        Route::delete( 'entities' , [ EntityController::class , 'destroy' ] );
        Route::apiResource( 'transactions' , TransactionController::class )->except( 'destroy' );
        Route::delete( 'transactions' , [ TransactionController::class , 'destroy' ] );
        Route::apiResource( 'transactionCategories' , TransactionCategoryController::class )->except( 'destroy' );
        Route::delete( 'transactionCategories' , [ TransactionCategoryController::class , 'destroy' ] );
        Route::get( 'accounts' , [ MotherAccountController::class , 'all' ] );
        Route::get( 'logs' , [ ActivityLogController::class , 'index' ] );
        Route::get( 'dashboard' , [ DashboardController::class , 'index' ] );

        Route::prefix( 'settings' )->group( function () {
            Route::get( '/' , [ SettingsController::class , 'all' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/' , [ SettingsController::class , 'update' ] );
        } );
    } );
    //    Route::middleware( [
    //        'api' , 'auth:sanctum' ,
    //        InitializeTenancyByDomain::class ,
    //        PreventAccessFromCentralDomains::class ,
    //    ] )->prefix( 'api' )->group( function () {
    //        Route::apiResource( 'motherAccounts' , MotherAccountController::class )->except( 'destroy' );
    //        Route::apiResource( 'subAccounts' , SubAccountController::class )->except( 'destroy' );
    //        Route::delete( 'subAccounts' , [ SubAccountController::class , 'destroy' ] );
    //        Route::apiResource( 'currencies' , CurrencyController::class )->except( 'destroy' );
    //        Route::delete( 'currencies' , [ CurrencyController::class , 'destroy' ] );
    //        Route::apiResource( 'entities' , EntityController::class )->except( 'destroy' );
    //        Route::apiResource( 'transactions' , TransactionController::class )->except( 'destroy' );
    //        Route::delete( 'transactions' , [ TransactionController::class , 'destroy' ] );
    //        Route::apiResource( 'transactionCategories' , TransactionCategoryController::class )->except( 'destroy' );
    //        Route::delete( 'transactionCategories' , [ TransactionCategoryController::class , 'destroy' ] );
    //        Route::get( 'accounts' , [ MotherAccountController::class , 'all' ] );
    //        Route::get( 'logs' , [ ActivityLogController::class , 'index' ] );
    //        Route::get('dashboard', [DashboardController::class, 'index']);
    //
    //        Route::prefix( 'settings' )->group( function () {
    //            Route::get( '/' , [ SettingsController::class , 'all' ] );
    //            Route::match( [ 'post' , 'put' , 'patch' ] , '/' , [ SettingsController::class , 'update' ] );
    //        } );
    //    } );
