<?php

    use App\Http\Controllers\ActivityLogController;
    use App\Http\Controllers\Admin\AdministratorAddressController;
    use App\Http\Controllers\Admin\AdministratorController;
    use App\Http\Controllers\Admin\BarcodeController;
    use App\Http\Controllers\Admin\BranchController;
    use App\Http\Controllers\Admin\CityController;
    use App\Http\Controllers\Admin\CompanyController;
    use App\Http\Controllers\Admin\CountryCodeController;
    use App\Http\Controllers\Admin\CountryController;
    use App\Http\Controllers\Admin\CurrencyController;
    use App\Http\Controllers\Admin\CustomerAddressController;
    use App\Http\Controllers\Admin\CustomerController;
    use App\Http\Controllers\Admin\DamageController;
    use App\Http\Controllers\Admin\DashboardController;
    use App\Http\Controllers\Admin\EmployeeAddressController;
    use App\Http\Controllers\Admin\EmployeeController;
    use App\Http\Controllers\Admin\IngredientsController;
    use App\Http\Controllers\Admin\LanguageController;
    use App\Http\Controllers\Admin\MailController;
    use App\Http\Controllers\Admin\MenuSectionController;
    use App\Http\Controllers\Admin\MenuTemplateController;
    use App\Http\Controllers\Admin\MyOrderDetailsController;
    use App\Http\Controllers\Admin\NotificationAlertController;
    use App\Http\Controllers\Admin\NotificationController;
    use App\Http\Controllers\Admin\OtpController;
    use App\Http\Controllers\Admin\PaymentGatewayController;
    use App\Http\Controllers\Admin\PermissionController;
    use App\Http\Controllers\Admin\PosController;
    use App\Http\Controllers\Admin\PosOrderController;
    use App\Http\Controllers\Admin\ProductAttributeController;
    use App\Http\Controllers\Admin\ProductAttributeOptionController;
    use App\Http\Controllers\Admin\ProductBrandController;
    use App\Http\Controllers\Admin\ProductCategoryController;
    use App\Http\Controllers\Admin\ProductController;
    use App\Http\Controllers\Admin\ProductsReportController;
    use App\Http\Controllers\Admin\ProductVariationController;
    use App\Http\Controllers\Admin\PurchaseController;
    use App\Http\Controllers\Admin\RoleController;
    use App\Http\Controllers\Admin\SalesReportController;
    use App\Http\Controllers\Admin\SimpleUserController;
    use App\Http\Controllers\Admin\SiteController;
    use App\Http\Controllers\Admin\SmsGatewayController;
    use App\Http\Controllers\Admin\StateController;
    use App\Http\Controllers\Admin\StockController;
    use App\Http\Controllers\Admin\SupplierController;
    use App\Http\Controllers\Admin\TaxController;
    use App\Http\Controllers\Admin\ThemeController;
    use App\Http\Controllers\Admin\TimezoneController;
    use App\Http\Controllers\Admin\UnitController;
    use App\Http\Controllers\API\DistributionRouteController;
    use App\Http\Controllers\Auth\ForgotPasswordController;
    use App\Http\Controllers\Auth\LoginController;
    use App\Http\Controllers\Auth\RefreshTokenController;
    use App\Http\Controllers\ChartOfAccountGroupController;
    use App\Http\Controllers\CleaningOrderController;
    use App\Http\Controllers\CleaningServiceCategoryController;
    use App\Http\Controllers\CleaningServiceController;
    use App\Http\Controllers\CleaningServiceCustomerController;
    use App\Http\Controllers\CommissionController;
    use App\Http\Controllers\CommissionPayoutController;
    use App\Http\Controllers\CreditDepositPurchaseController;
    use App\Http\Controllers\CustomerPaymentController;
    use App\Http\Controllers\ExpenseCategoryController;
    use App\Http\Controllers\ExpensePaymentController;
    use App\Http\Controllers\ExpensesController;
    use App\Http\Controllers\Frontend\LanguageController as FrontendLanguageController;
    use App\Http\Controllers\Frontend\ProfileController;
    use App\Http\Controllers\Frontend\SettingController as FrontendSettingController;
    use App\Http\Controllers\IotecController;
    use App\Http\Controllers\LedgerController;
    use App\Http\Controllers\ModuleController;
    use App\Http\Controllers\PaymentAccountController;
    use App\Http\Controllers\PaymentController;
    use App\Http\Controllers\PaymentMethodController;
    use App\Http\Controllers\ProductionController;
    use App\Http\Controllers\ProductionProcessController;
    use App\Http\Controllers\ProductionSetupController;
    use App\Http\Controllers\StockTransferController;
    use App\Http\Controllers\SubscriptionController;
    use App\Http\Controllers\UnitConversionController;
    use App\Http\Controllers\WarehouseController;
    use App\Http\Controllers\WhatsAppController;
    use App\Http\Resources\ChartOfAccountGroupResource;
    use App\Models\ChartOfAccountGroup;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;

    Route::post( 'success' , [ IotecController::class , 'success' ] );
    Route::post( 'pay' , [ IotecController::class , 'pay' ] );
    Route::get( 'cleaningServiceCustomer' , [ CleaningServiceCustomerController::class , 'customer' ] )->withoutMiddleware( 'auth' );
    Route::get( 'cleaningServiceCategories' , [ CleaningServiceCategoryController::class , 'index' ] )->withoutMiddleware( 'auth' );
    Route::get( 'cleaningServiceCategories/list' , [ CleaningServiceCategoryController::class , 'list' ] );
    Route::post( 'clientCleaningOrders' , [ CleaningOrderController::class , 'storeClient' ] );
    Route::middleware( [ 'auth:sanctum' ] )->get( '/user' , function (Request $request) {
        return $request->user()->load( 'roles' );
    } );
    Route::get( '/check' , [ PurchaseController::class , 'index' ] );
    Route::get( '/p' , [ ProductController::class , 'index' ] );

    Route::get( 'company' , [ CompanyController::class , 'index' ] );
    Route::get( 'site' , [ SiteController::class , 'index' ] );
    Route::get( 'cleaningOrder' , [ CleaningOrderController::class , 'order' ] );

    Route::get( 'whatsapp' , [ WhatsAppController::class , 'index' ] )->name( 'whats-app.index' );
    Route::post( 'whatsapp' , [ WhatsAppController::class , 'message' ] )->name( 'whats-app.message' );

    Route::get( 'coa' , function () {

        return ChartOfAccountGroupResource::collection( ChartOfAccountGroup::whereNull( 'parent_id' )
                                                                           ->with( [ 'childrenRecursive' , 'ledgers' ] )
                                                                           ->get() );
    } );
    Route::get( 'pdf/{order}' , [ PosOrderController::class , 'pdf' ] );
    Route::match( [ 'get' , 'post' ] , '/login' , function () {
        return response()->json( [ 'errors' => 'unauthenticated' ] , 401 );
    } )->name( 'login_auth' );
    Route::apiResource( 'subscriptions' , SubscriptionController::class );
    Route::get( 'subscriptionPlans' , [ SubscriptionController::class , 'subscriptionPlans' ] );
    Route::get( 'has-active' , [ SubscriptionController::class , 'hasActive' ] );
    Route::apiResource( 'expense-categories' , ExpenseCategoryController::class );

//    Route::post('pay' , [ PaymentController::class , 'requestToPay' ]);
    Route::post( 'status' , [ PaymentController::class , 'requesttoPayTransactionStatus' ] );

    Route::match( [ 'get' , 'post' ] , '/refresh-token' , [ RefreshTokenController::class , 'refreshToken' ] )->middleware( [ 'installed' ] );

    Route::prefix( 'auth' )->name( 'auth.' )->namespace( 'Auth' )->group( function () {
        Route::post( '/login' , [ LoginController::class , 'login' ] );
        Route::post( 'token' , [ LoginController::class , 'token' ] );
        Route::prefix( 'forgot-password' )->name( 'forgot-password-auth.' )->group( function () {
            Route::post( '/' , [ ForgotPasswordController::class , 'forgotPassword' ] );
            Route::post( '/otp-phone' , [ ForgotPasswordController::class , 'otpPhone' ] );
            Route::post( '/otp-email' , [ ForgotPasswordController::class , 'otpEmail' ] );
            Route::post( '/verify-phone' , [ ForgotPasswordController::class , 'verifyPhone' ] );
            Route::post( '/verify-email' , [ ForgotPasswordController::class , 'verifyEmail' ] );
            Route::post( '/reset-password' , [ ForgotPasswordController::class , 'resetPassword' ] );
            Route::post( '/reset-pin' , [ ForgotPasswordController::class , 'resetPin' ] );
        } );

        Route::middleware( 'auth:sanctum' )->group( function () {
            Route::post( '/logout' , [ LoginController::class , 'logout' ] );
        } );

        Route::post( '/authcheck' , function () {
            if ( Auth::check() ) {
                return response()->json( [ 'status' => TRUE ] );
            }
            return response()->json( [ 'status' => FALSE ] );
        } );
    } );

    /* all routes must be singular word*/
    Route::prefix( 'profile' )->name( 'profile.' )->middleware( [ 'auth:sanctum' ] )->group( function () {
        Route::get( '/' , [ ProfileController::class , 'profile' ] );
        Route::match( [ 'post' , 'put' , 'patch' ] , '/' , [ ProfileController::class , 'update' ] );
        Route::match( [ 'put' , 'patch' ] , '/change-password' , [ ProfileController::class , 'changePassword' ] );
        Route::post( '/change-image' , [ ProfileController::class , 'changeImage' ] );
    } );

    Route::prefix( 'admin' )->name( 'admin.' )->middleware( [ 'local.auth' , 'auth:sanctum' ] )->group( function () {
//    Route::prefix( 'admin' )->name( 'admin.' )->middleware( [ 'local.auth' ] )->group( function () {
        Route::prefix( 'timezone' )->name( 'timezone.' )->group( function () {
            Route::get( '/' , [ TimezoneController::class , 'index' ] );
        } );
        Route::prefix( 'branches' )->name( 'branches.' )->group( function () {
            Route::get( '/' , [ BranchController::class , 'branches' ] );
            Route::post( '/' , [ BranchController::class , 'store' ] );
            Route::put( '/{branch}' , [ BranchController::class , 'update' ] );
            Route::delete( '/delete' , [ BranchController::class , 'destroy' ] );
        } );

        Route::get( '/menu' , [ LoginController::class , 'menu' ] );

        Route::get( 'cleaningOrder' , [ CleaningOrderController::class , 'order' ] );
        Route::apiResource( 'cleaningOrders' , CleaningOrderController::class )->except( [ 'destroy' , 'update' ] );
        Route::put( 'cleaningOrders/{cleaningOrder}' , [ CleaningOrderController::class , 'update' ] );
        Route::get( 'cleaningServiceCategories/list' , [ CleaningServiceCategoryController::class , 'list' ] );
        Route::apiResource( 'cleaningServiceCategories' , CleaningServiceCategoryController::class )->except( [ 'destroy' , ] );
        Route::get( 'cleaningServices/{category}' , [ CleaningServiceController::class , 'cleaningServicesByCategory' ] );
        Route::apiResource( 'cleaningServices' , CleaningServiceController::class )->except( [ 'destroy' ] );
        Route::delete( 'cleaningServiceCategories/delete' , [ CleaningServiceCategoryController::class , 'destroy' ] );
        Route::delete( 'cleaningServices/delete' , [ CleaningServiceController::class , 'destroy' ] );
        Route::delete( 'cleaningOrders/delete' , [ CleaningOrderController::class , 'destroy' ] );

        Route::apiResource( '/distributionRoutes' , DistributionRouteController::class );
        Route::apiResource( '/commissions' , CommissionController::class );
        Route::apiResource( '/commissionPayouts' , CommissionPayoutController::class );
        Route::get( '/commission-summary' , [ CommissionController::class , 'commissionSummary' ] );
        Route::get( '/commission-summary-dashboard' , [ CommissionController::class , 'commissionSummaryDashboard' ] );
        Route::get( '/truck-stock' , [ DistributionRouteController::class , 'truckStock' ] );

        Route::resource( '/payment-accounts' , PaymentAccountController::class );
        Route::resource( '/ledger-groups' , ChartOfAccountGroupController::class );
        Route::resource( '/ledgers' , LedgerController::class );
        Route::get( '/ledger-transactions/{ledger}' , [ LedgerController::class , 'transactions' ] );
        Route::get( '/ledgerGroups' , [ ChartOfAccountGroupController::class , 'groups' ] );
        Route::get( '/payment-methods' , [ PurchaseController::class , 'paymentMethods' ] );
        Route::get( '/taxes' , [ PurchaseController::class , 'taxes' ] );
        Route::get( '/activityLogs' , [ ActivityLogController::class , 'index' ] );
        Route::post( '/pos-payment' , [ PurchaseController::class , 'pos' ] );

        Route::prefix( 'dashboard' )->name( 'dashboard.' )->group( function () {
            Route::get( '/cards' , [ DashboardController::class , 'cards' ] );
            Route::get( '/total-sales' , [ DashboardController::class , 'totalSales' ] );
            Route::get( '/total-expenses' , [ DashboardController::class , 'totalExpenses' ] );
            Route::get( '/pending-expenses' , [ DashboardController::class , 'pendingExpenses' ] );
            Route::get( '/total-orders' , [ DashboardController::class , 'totalOrders' ] );
            Route::get( '/total-customers' , [ DashboardController::class , 'totalCustomers' ] );
            Route::get( '/total-products' , [ DashboardController::class , 'totalProducts' ] );
            Route::get( '/sales-summary' , [ DashboardController::class , 'salesSummary' ] );
            Route::get( '/customer-states' , [ DashboardController::class , 'customerStates' ] );
            Route::get( '/top-products' , [ DashboardController::class , 'topProducts' ] );
            Route::get( '/credit-sales' , [ DashboardController::class , 'creditSales' ] );
            Route::get( '/deposit-sales' , [ DashboardController::class , 'depositSales' ] );
            Route::get( '/in-stock' , [ DashboardController::class , 'inStock' ] );
            Route::get( '/out-stock' , [ DashboardController::class , 'outStock' ] );
            Route::get( '/expiredStock' , [ DashboardController::class , 'expiredStock' ] );
            Route::get( '/stock-value' , [ DashboardController::class , 'stockValue' ] );
            Route::get( '/vendor-balance' , [ DashboardController::class , 'vendorBalance' ] );
            Route::get( '/net-profit' , [ DashboardController::class , 'netProfit' ] );
            Route::get( '/gross-profit' , [ DashboardController::class , 'grossProfit' ] );
        } );

        Route::prefix( 'supplier' )->name( 'supplier.' )->group( function () {
            Route::get( '/' , [ SupplierController::class , 'index' ] );
            Route::get( '/show/{supplier}' , [ SupplierController::class , 'show' ] );
            Route::post( '/' , [ SupplierController::class , 'store' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/{supplier}' , [ SupplierController::class , 'update' ] );
            Route::delete( '/delete' , [ SupplierController::class , 'destroy' ] );
        } );

        Route::prefix( 'setting' )->name( 'setting.' )->withoutMiddleware( [ 'subscribed' ] )->group( function () {
            Route::prefix( 'company' )->name( 'company.' )->group( function () {
                Route::get( '/' , [ CompanyController::class , 'index' ] );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/' , [ CompanyController::class , 'update' ] );
            } );
            Route::prefix( 'payment-methods' )->name( 'payment-methods.' )->group( function () {
                Route::get( '/' , [ PaymentMethodController::class , 'index' ] );
                Route::post( '/' , [ PaymentMethodController::class , 'store' ] );
                Route::post( '/transfer' , [ PaymentMethodController::class , 'transfer' ] );
                Route::match(
                    [ 'put' , 'patch' ] ,
                    '/{method}' ,
                    [ PaymentMethodController::class , 'update' ]
                );
                Route::delete( '/delete' , [ PaymentMethodController::class , 'deleteMethods' ] );
            } );
            Route::prefix( 'payment-gateway' )->name( 'payment-gateway.' )->group( function () {
                Route::get( '/' , [ PaymentGatewayController::class , 'index' ] );
                Route::match( [ 'put' , 'patch' ] , '/' , [ PaymentGatewayController::class , 'update' ] );
            } );
            Route::prefix( 'site' )->name( 'site.' )->group( function () {
                Route::get( '/' , [ SiteController::class , 'index' ] );
                Route::match( [ 'post' , 'patch' ] , '/' , [ SiteController::class , 'update' ] );
            } );

            Route::prefix( 'module' )->name( 'module.' )->group( function () {
                Route::get( '/' , [ ModuleController::class , 'index' ] );
                Route::match( [ 'put' , 'patch' ] , '/' , [ ModuleController::class , 'update' ] );
            } );
            Route::prefix( 'appSettings' )->name( 'appSettings.' )->group( function () {
                Route::get( '/' , [ ModuleController::class , 'appSettings' ] );
                Route::match( [ 'put' , 'patch' ] , '/' , [ ModuleController::class , 'updateAppSettings' ] );
            } );

            Route::prefix( 'theme' )->name( 'theme.' )->group( function () {
                Route::get( '/' , [ ThemeController::class , 'index' ] );
                Route::post( '/' , [ ThemeController::class , 'update' ] );
            } );

            Route::prefix( 'sms-gateway' )->name( 'sms-gateway.' )->group( function () {
                Route::get( '/' , [ SmsGatewayController::class , 'index' ] );
                Route::post( '/' , [ SmsGatewayController::class , 'update' ] );
                Route::post( '/test' , [ SmsGatewayController::class , 'test' ] );
            } );

            Route::prefix( 'mail' )->name( 'mail.' )->group( function () {
                Route::get( '/' , [ MailController::class , 'index' ] );
                Route::match( [ 'post' , 'patch' ] , '/' , [ MailController::class , 'update' ] );
            } );

            Route::prefix( 'notification' )->name( 'notification.' )->group( function () {
                Route::get( '/' , [ NotificationController::class , 'index' ] );
                Route::match( [ 'put' , 'patch' ] , '/' , [ NotificationController::class , 'update' ] );
                Route::post( '/channels' , [ NotificationController::class , 'updateChannels' ] );
            } );

            Route::prefix( 'notification' )->name( 'notification-alert.' )->group( function () {
                Route::get( '/' , [ NotificationAlertController::class , 'index' ] );
                Route::match( [ 'post' , 'patch' ] , '/' , [ NotificationAlertController::class , 'update' ] );
            } );

            Route::prefix( 'otp' )->name( 'otp.' )->group( function () {
                Route::get( '/' , [ OtpController::class , 'index' ] );
                Route::match( [ 'post' , 'patch' ] , '/' , [ OtpController::class , 'update' ] );
            } );


            Route::prefix( 'currency' )->name( 'currency.' )->group( function () {
                Route::get( '/' , [ CurrencyController::class , 'index' ] );
                Route::get( '/show/{currency}' , [ CurrencyController::class , 'show' ] );
                Route::post( '/' , [ CurrencyController::class , 'store' ] );
                Route::post( '/base/{currency}' , [ CurrencyController::class , 'setBase' ] );
                Route::match( [ 'put' , 'patch' ] , '/{currency}' , [ CurrencyController::class , 'update' ] );
                // Route::delete( '/{currency}' , [ CurrencyController::class , 'destroy' ] );
                Route::delete( '/delete' , [ CurrencyController::class , 'deleteMethods' ] );
            } );

            Route::prefix( 'tax' )->name( 'tax.' )->group( function () {
                Route::get( '/' , [ TaxController::class , 'index' ] );
                Route::get( '/show/{tax}' , [ TaxController::class , 'show' ] );
                Route::post( '/' , [ TaxController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{tax}' , [ TaxController::class , 'update' ] );
                Route::delete( '/delete' , [ TaxController::class , 'deleteMethods' ] );
            } );

            Route::prefix( 'product-category' )->name( 'product-category.' )->group( function () {
                Route::get( '/' , [ ProductCategoryController::class , 'index' ] );
                Route::get( '/list' , [ ProductCategoryController::class , 'list' ] );
                Route::get( '/depth-tree' , [ ProductCategoryController::class , 'depthTree' ] );
                Route::get( '/show/{productCategory}' , [ ProductCategoryController::class , 'show' ] );
                Route::post( '/' , [ ProductCategoryController::class , 'store' ] );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/{productCategory}' , [ ProductCategoryController::class , 'update' ] );
                Route::delete( '/delete' , [ ProductCategoryController::class , 'destroy' ] );
                Route::get( '/ancestors-and-self/{productCategory:slug}' , [ ProductCategoryController::class , 'ancestorsAndSelf' ] );
                Route::get( '/tree' , [ ProductCategoryController::class , 'tree' ] );
                Route::get( '/export' , [ ProductCategoryController::class , 'export' ] );
                Route::get( '/download-attachment' , [ ProductCategoryController::class , 'downloadAttachment' ] );
                Route::post( '/import/file' , [ ProductCategoryController::class , 'import' ] );
            } );

            Route::prefix( 'product-brand' )->name( 'product-brand.' )->group( function () {
                Route::get( '/' , [ ProductBrandController::class , 'index' ] );
                Route::get( '/list' , [ ProductBrandController::class , 'list' ] );
                Route::get( '/show/{productBrand}' , [ ProductBrandController::class , 'show' ] );
                Route::post( '/' , [ ProductBrandController::class , 'store' ] );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/{productBrand}' , [ ProductBrandController::class , 'update' ] );
                Route::delete( '/delete' , [ ProductBrandController::class , 'destroy' ] );
//                Route::delete( '/{productBrand}' , [ ProductBrandController::class , 'destroy' ] );
            } );

            Route::prefix( 'language' )->name( 'language.' )->group( function () {
                Route::get( '/' , [ LanguageController::class , 'index' ] );
                Route::post( '/' , [ LanguageController::class , 'store' ] );
                Route::get( '/show/{language}' , [ LanguageController::class , 'show' ] );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/update/{language}' , [ LanguageController::class , 'update' ] );
                Route::delete( '/{language}' , [ LanguageController::class , 'destroy' ] );

                Route::get( '/file-list/{language:code}' , [ LanguageController::class , 'fileList' ] );
                Route::post( '/file-text' , [ LanguageController::class , 'fileText' ] );
                Route::post( '/file-text/store' , [ LanguageController::class , 'fileTextStore' ] );
            } );

            Route::prefix( 'menu-section' )->name( 'menu-section.' )->group( function () {
                Route::get( '/' , [ MenuSectionController::class , 'index' ] );
            } );

            Route::prefix( 'menu-template' )->name( 'menu-template.' )->group( function () {
                Route::get( '/' , [ MenuTemplateController::class , 'index' ] );
                Route::get( '/show/{menuTemplate}' , [ MenuTemplateController::class , 'show' ] );
                Route::post( '/' , [ MenuTemplateController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{menuTemplate}' , [ MenuTemplateController::class , 'update' ] );
                Route::delete( '/{menuTemplate}' , [ MenuTemplateController::class , 'destroy' ] );
            } );

            Route::prefix( 'product-attribute' )->name( 'product-attribute.' )->group( function () {
                Route::get( '/' , [ ProductAttributeController::class , 'index' ] );
                Route::get( '/show/{productAttribute}' , [ ProductAttributeController::class , 'show' ] );
                Route::post( '/' , [ ProductAttributeController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{productAttribute}' , [ ProductAttributeController::class , 'update' ] );
                Route::delete( '/delete' , [ ProductAttributeController::class , 'destroy' ] );
            } );

            Route::prefix( 'product-attribute-option' )->name( 'product-attribute-option.' )->group( function () {
                Route::get( '/{productAttribute}' , [ ProductAttributeOptionController::class , 'index' ] );
                Route::get( '/{productAttribute}/show/{productAttributeOption}' , [ ProductAttributeOptionController::class , 'show' ] );
                Route::post( '/{productAttribute}' , [ ProductAttributeOptionController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{productAttribute}/{productAttributeOption}' , [ ProductAttributeOptionController::class , 'update' ] );
//                Route::delete( '/{productAttribute}/{productAttributeOption}/delete' , [ ProductAttributeOptionController::class , 'destroy' ] );
                Route::delete( '/delete' , [ ProductAttributeOptionController::class , 'destroy' ] );
            } );

            Route::prefix( 'unit' )->name( 'unit.' )->group( function () {
                Route::get( '/' , [ UnitController::class , 'index' ] );
                Route::get( '/list' , [ UnitController::class , 'list' ] );
                Route::get( '/show/{unit}' , [ UnitController::class , 'show' ] );
                Route::post( '/' , [ UnitController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{unit}' , [ UnitController::class , 'update' ] );
                Route::delete( '/delete' , [ UnitController::class , 'destroy' ] );
            } );
            Route::prefix( 'units_conversion' )->name( 'units_conversion.' )->group( function () {
                Route::get( '/' , [ UnitConversionController::class , 'index' ] );
                Route::get( '/show/{unitConversion}' , [ UnitConversionController::class , 'show' ] );
                Route::post( '/' , [ UnitConversionController::class , 'store' ] );
                Route::match( [ 'put' , 'patch' ] , '/{unitConversion}' , [ UnitConversionController::class , 'update' ] );
                Route::delete( '/{unitConversion}' , [ UnitConversionController::class , 'destroy' ] );
            } );

            Route::prefix( 'barcode' )->name( 'barcode.' )->group( function () {
                Route::get( '/' , [ BarcodeController::class , 'index' ] );
            } );

            Route::prefix( 'role' )->name( 'role.' )->group( function () {
                Route::get( '/' , [ RoleController::class , 'index' ] );
                Route::post( '/' , [ RoleController::class , 'store' ] );
                Route::get( '/show/{role}' , [ RoleController::class , 'show' ] );
                Route::match( [ 'put' , 'patch' ] , '/{role}' , [ RoleController::class , 'update' ] );
                Route::delete( '/delete' , [ RoleController::class , 'destroy' ] );
            } );

            Route::prefix( 'permission' )->name( 'permission.' )->group( function () {
                Route::get( '/{role}' , [ PermissionController::class , 'index_old' ] );
                Route::post( '/{role}' , [ PermissionController::class , 'update' ] );
//                Route::match( [ 'put' , 'patch' ] , '/{role}' , [ PermissionController::class , 'update' ] );
            } );
        } );

        Route::resource( 'expenses' , ExpensesController::class );
        Route::get( 'expense-category/depth-tree' , [ ExpenseCategoryController::class , 'depthTree' ] );
        Route::resource( 'expense-payments' , ExpensePaymentController::class );
        Route::get( 'expense-categories-export' , [ ExpenseCategoryController::class , 'export' ] );

        Route::prefix( 'product' )->name( 'product.' )->group( function () {
            Route::get( '/' , [ ProductController::class , 'index' ] );
            Route::get( '/show/{product}' , [ ProductController::class , 'show' ] );
            Route::get( '/pos-product/{product}' , [ ProductController::class , 'posProduct' ] );
            Route::post( '/' , [ ProductController::class , 'store' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/{product}' , [ ProductController::class , 'update' ] );
            Route::delete( '/' , [ ProductController::class , 'destroy' ] );
            Route::post( '/upload-image/{product}' , [ ProductController::class , 'uploadImage' ] );
            Route::get( '/delete-image/{product}/{index}' , [ ProductController::class , 'deleteImage' ] );
            Route::get( '/export' , [ ProductController::class , 'export' ] );
            Route::get( '/generate-sku/{barcodeMethod}' , [ ProductController::class , 'generateSku' ] );
            Route::post( '/offer/{product}' , [ ProductController::class , 'productOffer' ] );
            Route::get( '/purchasable-product' , [ ProductController::class , 'purchasableProducts' ] );
            Route::get( '/simple-product' , [ ProductController::class , 'simpleProducts' ] );
            Route::get( '/download-attachment' , [ ProductController::class , 'downloadAttachment' ] );
            Route::get( '/purchasable-ingredient' , [ ProductController::class , 'purchasableIngredients' ] );
            Route::post( '/import/file' , [ ProductController::class , 'import' ] );
            Route::get( '/download-barcode/{product}' , [ ProductController::class , 'downloadBarcode' ] );
            Route::get( '/barcode-product/{barcode}' , [ ProductController::class , 'barcodeProduct' ] );
            Route::prefix( 'variation' )->name( 'variation.' )->group( function () {
                Route::get( '/{product}' , [ ProductVariationController::class , 'index' ] );
                Route::get( '/{product}/tree' , [ ProductVariationController::class , 'tree' ] );
                Route::get( '/{product}/single-tree' , [ ProductVariationController::class , 'singleTree' ] );
                Route::get( '/{product}/tree-with-selected' , [ ProductVariationController::class , 'treeWithSelected' ] );
                Route::post( '/{product}/store' , [ ProductVariationController::class , 'store' ] );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/{product}/update/{productVariation}' , [ ProductVariationController::class , 'update' ] );
                Route::delete( '/{product}/destroy/{productVariation}' , [ ProductVariationController::class , 'destroy' ] );
                Route::get( '/{product}/show/{productVariation}' , [ ProductVariationController::class , 'show' ] );
                Route::get( '/ancestors-and-self/{productVariation}' , [ ProductVariationController::class , 'ancestorsToString' ] );
                Route::get( '/barcode-variation-product/{productVariation}' , [ ProductVariationController::class , 'barcodeVariationProduct' ] );
                Route::get( '/download-barcode/{productVariation}' , [ ProductVariationController::class , 'downloadBarcode' ] );
            } );
            Route::get( '/initial-variation/{product}' , [ ProductVariationController::class , 'initialVariation' ] );
            Route::get( '/children-variation/{productVariation}' , [ ProductVariationController::class , 'childrenVariation' ] );
            Route::get( '/ancestors-and-self-id/{productVariation}' , [ ProductVariationController::class , 'ancestorsAndSelfId' ] );
        } );


        Route::prefix( 'administrator' )->name( 'administrator.' )->group( function () {
            Route::get( '/' , [ AdministratorController::class , 'index' ] );
            Route::get( '/show/{administrator}' , [ AdministratorController::class , 'show' ] );
            Route::post( '/' , [ AdministratorController::class , 'store' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/{administrator}' , [ AdministratorController::class , 'update' ] );
            Route::delete( '/{administrator}' , [ AdministratorController::class , 'destroy' ] );
            Route::get( '/export' , [ AdministratorController::class , 'export' ] );
            Route::post( '/change-password/{administrator}' , [ AdministratorController::class , 'changePassword' ] );
            Route::post( '/change-image/{administrator}' , [ AdministratorController::class , 'changeImage' ] );
            Route::get( '/my-order/{administrator}' , [ AdministratorController::class , 'myOrder' ] );
            Route::get( '/address/{administrator}' , [ AdministratorAddressController::class , 'index' ] );
            Route::get( '/address/show/{administrator}/{address}' , [ AdministratorAddressController::class , 'show' ] );
            Route::post( '/address/{administrator}' , [ AdministratorAddressController::class , 'store' ] );
            Route::match( [ 'put' , 'patch' ] , '/address/{administrator}/{address}' , [ AdministratorAddressController::class , 'update' ] );
            Route::delete( '/address/{administrator}/{address}' , [ AdministratorAddressController::class , 'destroy' ] );
        } );

        Route::prefix( 'country' )->name( 'country.' )->group( function () {
            Route::get( '/' , [ CountryController::class , 'index' ] )->name( 'index' );
            Route::get( '/list' , [ CountryController::class , 'list' ] )->name( 'list' );
            Route::get( '/show/{country}' , [ CountryController::class , 'show' ] )->name( 'show' );
            Route::post( '/' , [ CountryController::class , 'store' ] )->name( 'store' );
            Route::delete( '/{country}' , [ CountryController::class , 'destroy' ] )->name( 'destroy' );
            Route::match( [ 'put' , 'patch' , 'post' ] , '/{country}' , [ CountryController::class , 'update' ] )->name( 'update' );
        } );

        Route::prefix( 'country-code' )->name( 'country-code.' )->withoutMiddleware( [ 'subscribed' ] )->group( function () {
            Route::get( '/' , [ CountryCodeController::class , 'index' ] );
            Route::get( '/show/{country}' , [ CountryCodeController::class , 'show' ] );
            Route::get( '/calling-code/{callingCode}' , [ CountryCodeController::class , 'callingCode' ] );
        } );
        Route::prefix( 'state' )->name( 'state.' )->group( function () {
            Route::get( '/' , [ StateController::class , 'index' ] );
            Route::get( '/{country:code}' , [ StateController::class , 'state' ] );
            Route::get( '/simple-lists' , [ StateController::class , 'simpleLists' ] );
            Route::get( '/show/{state}' , [ StateController::class , 'show' ] );
            Route::post( '/' , [ StateController::class , 'store' ] );
            Route::delete( '/{state}' , [ StateController::class , 'destroy' ] );
            Route::match( [ 'put' , 'patch' , 'post' ] , '/{state}' , [ StateController::class , 'update' ] );
            Route::get( '/states/{country}' , [ StateController::class , 'statesByCountry' ] );
        } );

        Route::prefix( 'city' )->name( 'city.' )->group( function () {
            Route::get( '/' , [ CityController::class , 'index' ] );
            Route::get( '/{country:code}/{state:name}' , [ CityController::class , 'city' ] );
            Route::get( '/show/{city}' , [ CityController::class , 'show' ] );
            Route::post( '/' , [ CityController::class , 'store' ] );
            Route::delete( '/{city}' , [ CityController::class , 'destroy' ] );
            Route::match( [ 'put' , 'patch' , 'post' ] , '/{city}' , [ CityController::class , 'update' ] );
            Route::get( '/cities/{state}' , [ CityController::class , 'citiesByState' ] );
        } );

        Route::prefix( 'customer' )->name( 'customer.' )->group( function () {
            Route::get( '/' , [ CustomerController::class , 'index' ] );
            Route::post( '/' , [ CustomerController::class , 'store' ] );
            Route::get( '/show/{customer}' , [ CustomerController::class , 'show' ] );
            Route::get( '/credits/{customer}' , [ CustomerController::class , 'credits' ] );
            Route::post( '/payment/{customer}' , [ CustomerController::class , 'payment' ] );
            Route::get( '/payments/{customer}' , [ CustomerPaymentController::class , 'index' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/{customer}' , [ PosController::class , 'updateCustomer' ] );
            Route::delete( 'delete' , [ CustomerController::class , 'destroy' ] );
            Route::get( '/export' , [ CustomerController::class , 'export' ] );
            Route::post( '/change-password/{customer}' , [ CustomerController::class , 'changePassword' ] );
            Route::post( '/change-image/{customer}' , [ CustomerController::class , 'changeImage' ] );
            Route::get( '/my-order/{customer}' , [ CustomerController::class , 'myOrder' ] );
            Route::get( '/address/{customer}' , [ CustomerAddressController::class , 'index' ] );
            Route::get( '/address/show/{customer}/{address}' , [ CustomerAddressController::class , 'show' ] );
            Route::post( '/address/{customer}' , [ CustomerAddressController::class , 'store' ] );
            Route::match( [ 'put' , 'patch' ] , '/address/{customer}/{address}' , [ CustomerAddressController::class , 'update' ] );
            Route::delete( '/address/{customer}/{address}' , [ CustomerAddressController::class , 'destroy' ] );
        } );


        Route::prefix( 'warehouse' )->name( 'warehouse.' )->group( function () {
            Route::get( '/' , [ WarehouseController::class , 'index' ] );
            Route::post( '/' , [ WarehouseController::class , 'store' ] );
            Route::get( '/show/{warehouse}' , [ WarehouseController::class , 'show' ] );
            Route::match( [ 'put' , 'patch' ] , '/{warehouse}' , [ WarehouseController::class , 'update' ] );
            Route::delete( '/{warehouse}' , [ WarehouseController::class , 'destroy' ] );
            Route::delete( '/delete' , [ WarehouseController::class , 'destroy' ] );
            Route::get( '/export' , [ WarehouseController::class , 'export' ] );
        } );


        Route::prefix( 'employee' )->name( 'employee.' )->group( function () {
            Route::get( '/' , [ EmployeeController::class , 'index' ] );
            Route::post( '/' , [ EmployeeController::class , 'store' ] );
            Route::get( '/show/{employee}' , [ EmployeeController::class , 'show' ] );
            Route::match( [ 'put' , 'patch' ] , '/{employee}' , [ EmployeeController::class , 'update' ] );
            Route::delete( '/{employee}' , [ EmployeeController::class , 'destroy' ] );
            Route::get( '/export' , [ EmployeeController::class , 'export' ] );
            Route::post( '/change-password/{employee}' , [ EmployeeController::class , 'changePassword' ] );
            Route::post( '/change-image/{employee}' , [ EmployeeController::class , 'changeImage' ] );
            Route::get( '/my-order/{employee}' , [ EmployeeController::class , 'myOrder' ] );
            Route::get( '/address/{employee}' , [ EmployeeAddressController::class , 'index' ] );
            Route::get( '/address/show/{employee}/{address}' , [ EmployeeAddressController::class , 'show' ] );
            Route::post( '/address/{employee}' , [ EmployeeAddressController::class , 'store' ] );
            Route::match( [ 'put' , 'patch' ] , '/address/{employee}/{address}' , [ EmployeeAddressController::class , 'update' ] );
            Route::delete( '/address/{employee}/{address}' , [ EmployeeAddressController::class , 'destroy' ] );
        } );

        Route::prefix( 'my-order' )->name( 'my-order.' )->group( function () {
            Route::get( '/show/{user}/{order}' , [ MyOrderDetailsController::class , 'orderDetails' ] );
        } );


        Route::prefix( 'sales-report' )->name( 'sales-report.' )->group( function () {
            Route::get( '/' , [ SalesReportController::class , 'index' ] );
            Route::get( '/export' , [ SalesReportController::class , 'export' ] );
            Route::get( '/overview' , [ SalesReportController::class , 'salesReportOverview' ] );
            Route::get( '/export-pdf' , [ SalesReportController::class , 'exportPdf' ] );
            Route::get( '/export-pdf' , [ SalesReportController::class , 'exportPdf' ] );
        } );

        Route::prefix( 'users' )->name( 'users.' )->group( function () {
            Route::get( '/' , [ SimpleUserController::class , 'index' ] );
        } );

        Route::prefix( 'purchase' )->name( 'purchase.' )->group( function () {
            Route::get( '/' , [ PurchaseController::class , 'index' ] );
            Route::post( '/' , [ PurchaseController::class , 'store' ] );
            Route::post( '/receive' , [ PurchaseController::class , 'receive' ] );
            Route::delete( '/delete' , [ PurchaseController::class , 'destroy' ] );
            Route::get( '/ingredients' , [ PurchaseController::class , 'indexIngredients' ] );
            Route::post( '/ingredient' , [ PurchaseController::class , 'storeIngredient' ] );
            Route::post( '/store-stock' , [ PurchaseController::class , 'storeStock' ] );
            Route::post( '/transfer-stock' , [ PurchaseController::class , 'transferStock' ] );
            Route::post( '/reconcile-stock' , [ PurchaseController::class , 'reconcileStock' ] );
            Route::get( '/show/{type}/{purchase}' , [ PurchaseController::class , 'show' ] );
            Route::get( '/ingredient/show/{purchase}' , [ PurchaseController::class , 'showIngredient' ] );
            Route::get( '/show/{purchase}' , [ PurchaseController::class , 'show' ] );
            Route::get( '/edit/{purchase}' , [ PurchaseController::class , 'edit' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/update/{purchase}' , [ PurchaseController::class , 'update' ] );
//            Route::delete( '/{purchase}' , [ PurchaseController::class , 'destroy' ] );
            Route::get( '/export' , [ PurchaseController::class , 'export' ] );
            Route::get( '/download-attachment/{purchase}' , [ PurchaseController::class , 'downloadAttachment' ] );
            Route::get( '/payment/{type}/{purchase}' , [ PurchaseController::class , 'paymentHistory' ] );
            Route::post( '/payment/{purchase}' , [ PurchaseController::class , 'payment' ] )->middleware('register');
            Route::get( '/payment/download-attachment/{purchasePayment}' , [ PurchaseController::class , 'paymentDownloadAttachment' ] );
            Route::delete( '/payment/{type}/{purchase}/{purchasePayment}' , [ PurchaseController::class , 'paymentDestroy' ] );
        } );

        Route::prefix( 'ingredients' )->name( 'ingredients.' )->group( function () {
            Route::get( '/' , [ IngredientsController::class , 'index' ] );
            Route::get( '/show/{ingredient}' , [ IngredientsController::class , 'show' ] );
            Route::post( '/' , [ IngredientsController::class , 'store' ] );
            Route::match( [ 'post' , 'put' , 'patch' ] , '/{ingredient}' , [ IngredientsController::class , 'update' ] );
            Route::delete( '/{ingredient}' , [ IngredientsController::class , 'destroy' ] );
            Route::get( '/export' , [ ProductController::class , 'export' ] );
        } );
        Route::resource( 'stockTransfer' , StockTransferController::class );

        Route::prefix( 'stock' )->name( 'stock.' )->group( function () {
            Route::get( '/' , [ StockController::class , 'index' ] );
            Route::get( '/takings' , [ StockController::class , 'takings' ] );
            Route::get( '/expiryList' , [ StockController::class , 'expiryList' ] );
            Route::get( '/expiryList/export' , [ StockController::class , 'expiryReportExport' ] );
            Route::get( '/transfers' , [ StockController::class , 'stockTransfers' ] );
            Route::get( '/reconciliations' , [ StockController::class , 'stockReconciliations' ] );
            Route::post( '/transfer/cancelOrAccept' , [ StockController::class , 'cancelOrAccept' ] );
            Route::post( '/transfer/approve' , [ StockController::class , 'approveStockRequest' ] );
            Route::post( '/transfer/receive' , [ StockController::class , 'receiveStockRequest' ] );
            Route::get( '/transfer/show' , [ StockController::class , 'showStockTransfer' ] );
            Route::get( '/export' , [ StockController::class , 'export' ] );
            Route::post( '/ingredients' , [ StockController::class , 'storeIngredientStock' ] );
            Route::post( '/items' , [ StockController::class , 'storeItemStock' ] );
            Route::get( '/ingredients' , [ StockController::class , 'indexIngredients' ] );
            Route::get( '/export' , [ StockController::class , 'export' ] );
        } );

        Route::prefix( 'damage' )->name( 'damage.' )->group( function () {
            Route::controller( DamageController::class )->group( function () {
                Route::get( '/' , 'index' )->name( 'index' );
                Route::post( '/' , 'store' )->name( 'store' );
                Route::get( '/show/{damage}' , 'show' );
                Route::post( '/status/{damage}' , 'updateStatus' );
                Route::get( '/edit/{damage}' , 'edit' );
                Route::match( [ 'post' , 'put' , 'patch' ] , '/update/{damage}' , 'update' );
                Route::delete( '/{damage}' , 'destroy' );
                Route::get( '/export' , [ DamageController::class , 'export' ] );
                Route::get( '/download-attachment/{damage}' , 'downloadAttachment' );
            } );
        } );

        Route::prefix( 'production' )->name( 'production.' )->group( function () {
            Route::get( 'items' , [ ProductionController::class , 'items' ] );
            Route::get( 'processing' , [ ProductionController::class , 'processing' ] );
            Route::get( 'report' , [ ProductionProcessController::class , 'report' ] );
            Route::get( 'ingredients' , [ ProductionController::class , 'ingredients' ] );
            Route::resource( 'productionSetups' , ProductionSetupController::class );
            Route::resource( 'productionProcesses' , ProductionProcessController::class );
            Route::get( 'completed' , [ ProductionProcessController::class , 'completed' ] );
            Route::put( 'cancel/{process}' , [ ProductionProcessController::class , 'cancel' ] );
        } );

        Route::prefix( 'products-report' )->name( 'products-report.' )->group( function () {
            Route::get( '/' , [ ProductsReportController::class , 'index' ] );
            Route::get( '/export' , [ ProductsReportController::class , 'export' ] );
            Route::get( '/overview' , [ ProductsReportController::class , 'productsReportOverview' ] );
            Route::get( '/export-pdf' , [ ProductsReportController::class , 'exportPdf' ] );
        } );

        Route::prefix( 'pos-order' )->name( 'posOrder.' )->group( function () {
            Route::get( '/' , [ PosOrderController::class , 'index' ] );
            Route::get( '/credits' , [ PosOrderController::class , 'indexCredit' ] );
            Route::get( '/quotations' , [ PosOrderController::class , 'indexQuotations' ] );
            Route::get( '/export-quotation' , [ PosOrderController::class , 'exportOrder' ] );
            Route::post( '/mail-quotation' , [ PosOrderController::class , 'mailQuotation' ] );
            Route::post( '/quotation-css-variables' , [ PosOrderController::class , 'updateCssVariables' ] );
            Route::get( '/deposits' , [ PosOrderController::class , 'indexDeposit' ] );
            Route::get( 'show/{order}' , [ PosOrderController::class , 'show' ] );
            Route::delete( '/{order}' , [ PosOrderController::class , 'destroy' ] );
            Route::get( '/export' , [ PosOrderController::class , 'export' ] );
            Route::put( '/change-status/{order}' , [ PosOrderController::class , 'changeStatus' ] );
            Route::post( '/change-payment-status/{order}' , [ PosOrderController::class , 'changePaymentStatus' ] );
            Route::get( '/payment/{order}' , [ CreditDepositPurchaseController::class , 'index' ] );
            Route::post( '/payment/{order}' , [ CreditDepositPurchaseController::class , 'updateBalance' ] )->middleware( 'register' );
        } );

        Route::prefix( 'pos' )->name( 'pos.' )->group( function () {
            Route::post( '/' , [ PosController::class , 'store' ] )->middleware( 'register' );
            Route::post( '/update' , [ PosController::class , 'update' ] );
            Route::post( '/open-register' , [ PosController::class , 'openRegister' ] );
            Route::get( '/register-details' , [ PosController::class , 'registerDetails' ] );
            Route::post( '/close-register' , [ PosController::class , 'closeRegister' ] );
            Route::post( '/makeSale' , [ PosController::class , 'makeSale' ] );
            Route::post( '/cancel' , [ PosController::class , 'cancel' ] );
            Route::post( '/customer' , [ PosController::class , 'storeCustomer' ] );
            Route::put( '/customer/{customer}' , [ PosController::class , 'updateCustomer' ] );
            Route::get( '/{order}' , [ PosController::class , 'index' ] );
        } );
    } );

    Route::prefix( 'frontend' )->name( 'frontend.' )->group( function () {
        Route::prefix( 'setting' )->name( 'setting.' )->group( function () {
            Route::get( '/' , [ FrontendSettingController::class , 'index' ] );
        } );

        Route::prefix( 'language' )->name( 'language.' )->group( function () {
            Route::get( '/' , [ FrontendLanguageController::class , 'index' ] );
            Route::get( '/show/{language}' , [ FrontendLanguageController::class , 'show' ] );
        } );
    } );
