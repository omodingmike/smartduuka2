<?php

    namespace App\Http\Middleware;

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
            $tenant   = tenant( 'id' );
            if ( $response instanceof JsonResponse && $tenant ) {
                $originalData = $response->getData( TRUE );

                $originalData[ 'currency' ]          = Settings::group( 'site' )->get( 'site_default_currency_symbol' );
                $originalData[ 'business_id' ]       = config( 'app.business_id' );
                $originalData[ 'print_agent_token' ] = config( 'app.print_agent_token' );

                $response->setData( $originalData );
            }
            return $response;
        }
    }
