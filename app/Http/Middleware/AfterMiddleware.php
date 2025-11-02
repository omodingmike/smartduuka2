<?php

    namespace App\Http\Middleware;

    use App\Enums\Activity;
    use App\Enums\Ask;
    use App\Enums\Modules;
    use App\Enums\SettingsEnum;
    use App\Models\Subscription;
    use Closure;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Smartisan\Settings\Facades\Settings;
    use Symfony\Component\HttpFoundation\Response;

    class AfterMiddleware
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $response = $next( $request );
            if ( $response instanceof JsonResponse ) {
                $originalData                   = $response->getData( TRUE );
                $subscription                   = Subscription::where( 'project_id' , config( 'app.project_id' ) )->latest()->first();
                $colors                         = [ 'primaryColor' , 'primaryLight' , 'secondaryColor' , 'secondaryLight' ];
                $originalData[ 'app_settings' ] = [
                    SettingsEnum::A_4_RECEIPT() => settingEnabled( SettingsEnum::A_4_RECEIPT() )
                ];
                foreach ( $colors as $color ) {
                    $originalData[ 'app_settings' ][ $color ] = settingValue( $color );
                }
                $originalData[ 'app_modules' ] = [
                    Modules::COMMISSION        => moduleEnabled( Modules::COMMISSION ) ,
                    Modules::DISTRIBUTION      => moduleEnabled( Modules::DISTRIBUTION ) ,
                    'module_wholesale'         => Settings::group( 'module' )->get( 'module_wholesale' ) == Activity::ENABLE ,
                    'module_warehouse'         => Settings::group( 'module' )->get( 'module_warehouse' ) == 1 ,
                    'normal_sell'              => Settings::group( 'site' )->get( 'site_sell' ) == 5 ,
                    'site_sell_from_warehouse' => Settings::group( 'site' )->get( 'site_sell_from_warehouse' ) == Ask::YES ,
                    'currency'                 => Settings::group( 'site' )->get( 'site_default_currency_symbol' ) ,
                    'business_id'              => config( 'app.business_id' ) ,
                    'project_id'               => config( 'app.project_id' ) ,
                    'code'                     => ledgerCode() ,
                    'subscription'             => [ 'status' => $subscription?->status , 'name' => $subscription?->plan?->name ] ,
                    'accounting'               => (boolean) config( 'app.accounting' ) ,
                ];
                $originalData[ 'system' ]      = [
                    'credits'    => config( 'system.credit' ) ,
                    'quotations' => config( 'system.quotations' ) ,
                ];
                $response->setData( $originalData );
            }
            return $response;
        }
    }
