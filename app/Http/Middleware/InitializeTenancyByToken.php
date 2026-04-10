<?php

    namespace App\Http\Middleware;

    use App\Models\CentralUser;
    use Closure;
    use Illuminate\Http\Request;
    use Laravel\Sanctum\PersonalAccessToken;

    class InitializeTenancyByToken
    {
        public function handle(Request $request , Closure $next)
        {
            $bearerToken = $request->bearerToken();

            if ( ! $bearerToken ) {
                return $next( $request );
            }

            $accessToken = PersonalAccessToken::findToken( $bearerToken );

            if ( ! $accessToken ) {
                return response()->json( [ 'message' => 'Invalid token' ] , 401 );
            }

            $tenantUser  = $accessToken->tokenable;
            $centralUser = CentralUser::where(
                $tenantUser->getGlobalIdentifierKeyName() ,
                $tenantUser->getGlobalIdentifierKey()
            )->first();

            if ( ! $centralUser ) {
                return response()->json( [ 'message' => 'Central user not found' ] , 401 );
            }

            $tenant = $centralUser->tenants()->first();

            if ( $tenant ) {
                tenancy()->initialize( $tenant );
            }

            return $next( $request );
        }
    }