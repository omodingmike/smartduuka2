<?php

    use App\Http\Controllers\Frontend\LanguageController as FrontendLanguageController;
    use App\Http\Controllers\Frontend\SettingController as FrontendSettingController;
    use App\Http\Controllers\IotecController;
    use App\Http\Controllers\SubscriptionController;
    use App\Http\Controllers\TenantController;
    use App\Http\Controllers\WhatsAppController;
    use Illuminate\Support\Facades\Route;

    Route::post( 'success' , [ IotecController::class , 'success' ] );
    Route::post( 'pay' , [ IotecController::class , 'pay' ] );

    Route::get( 'whatsapp' , [ WhatsAppController::class , 'index' ] )->name( 'whats-app.index' );
    Route::post( 'whatsapp' , [ WhatsAppController::class , 'message' ] )->name( 'whats-app.message' );

    Route::apiResource( 'subscriptions' , SubscriptionController::class );
    Route::get( 'subscriptionPlans' , [ SubscriptionController::class , 'subscriptionPlans' ] );
    Route::apiResource( 'tenants' , TenantController::class );

    Route::prefix( 'frontend' )->name( 'frontend.' )->group( function () {
        Route::prefix( 'setting' )->name( 'setting.' )->group( function () {
            Route::get( '/' , [ FrontendSettingController::class , 'index' ] );
        } );

        Route::prefix( 'language' )->name( 'language.' )->group( function () {
            Route::get( '/' , [ FrontendLanguageController::class , 'index' ] );
            Route::get( '/show/{language}' , [ FrontendLanguageController::class , 'show' ] );
        } );
    } );
