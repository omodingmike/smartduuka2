<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class AddCurrencySymbol
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $response = $next( $request );

            if ( $response instanceof JsonResponse ) {
                $data = $response->getData( TRUE );
                if ( is_array( $data ) ) {
                    $user                 = auth()->user();
                    $data[ 'currency' ]   = config( 'system.currency_symbol' );
                    $data[ 'currency_2' ] = env( 'CURRENCY_SYMBOL' );
                    if ( $user ) {
                        $data[ 'has_open_register' ] = $user->registers()->whereNull( 'closed_at' )->latest()->exists();;
                    }
                }
                $response->setData( $data );
            }

            return $response;
        }
    }
