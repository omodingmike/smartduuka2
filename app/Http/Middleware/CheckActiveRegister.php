<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class CheckActiveRegister
    {
        public function handle(Request $request , Closure $next) : Response
        {
            $user         = auth()->user();
            $openRegister = $user->registers()->whereNull( 'closed_at' )->latest()->first();
            if ( ! $openRegister ) {
                return response()->json( [
                    'message' => 'You do not have any open registers.'
                ] , 409 );
            }
            return $next( $request );
        }
    }
