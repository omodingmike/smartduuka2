<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Status;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\UserResource;
    use App\Models\CentralUser;
    use App\Models\User;
    use App\Services\PinService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Validator;
    use Laravel\Sanctum\PersonalAccessToken;

    class CentralLoginController extends Controller
    {
        public function login(Request $request , PinService $pin_service) : JsonResponse
        {
            try {
                $isPinLogin = $request->filled( 'pin' );
                $pin        = $request->string( 'pin' );
                $rules      = $isPinLogin
                    ? [ 'pin' => [ 'required' , 'string' , 'size:5' ] ]
                    : [ 'email' => [ 'required' , 'string' ] , 'password' => [ 'required' , 'string' ] ];

                $validator = Validator::make( $request->all() , $rules );
                if ( $validator->fails() ) {
                    return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
                }
                // 1. Authenticate against central DB
                if ( $isPinLogin ) {
                    $pin_hash    = $pin_service->hashPin( $pin );
                    $centralUser = CentralUser::where( 'pin' , $pin_hash )->first();
                    if ( ! $centralUser || ! $pin_service->verifyPin( $centralUser->pin , $pin ) ) {
                        $centralUser = NULL;
                    }
                }
                else {
                    $loginField  = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';
                    $centralUser = CentralUser::where( $loginField , $request->email )
                                              ->where( 'status' , Status::ACTIVE->value )
                                              ->first();

                    if ( ! $centralUser || ! \Hash::check( $request->password , $centralUser->password ) ) {
                        $centralUser = NULL;
                    }
                }

                if ( ! $centralUser ) {
                    return new JsonResponse( [
                        'errors' => [ 'validation' => trans( 'all.message.credentials_invalid' ) ]
                    ] , 401 );
                }

                // 2. Resolve tenant from central user
                $tenant = $centralUser->tenants()->first();

                if ( ! $tenant ) {
                    return new JsonResponse( [
                        'errors' => [ 'validation' => 'No tenant associated with this account' ]
                    ] , 403 );
                }

                if ( ! $tenant->database() ) {
                    return new JsonResponse([
                        'errors' => ['validation' => 'Tenant database not configured']
                    ], 500);
                }

                tenancy()->initialize( $tenant->id );

                $tenantUser = User::where(
                    $centralUser->getGlobalIdentifierKeyName() ,
                    $centralUser->getGlobalIdentifierKey()
                )->first();

                if ( ! $tenantUser ) {
                    tenancy()->end();
                    return new JsonResponse( [
                        'errors' => [ 'validation' => 'User not found in tenant' ]
                    ] , 404 );
                }

                // 4. Token logic — mirrors your LoginController exactly
                $token = $tenantUser->web_token;
                if ( $token ) {
                    $accessToken = PersonalAccessToken::findToken( $token );
                    if ( ! $accessToken ) {
                        $token = NULL;
                    }
                }
                if ( ! $token ) {
                    $token = $tenantUser->createToken( 'auth_token' )->plainTextToken;
                    $tenantUser->update( [ 'web_token' => $token ] );
                }

                $tenantUser->update( [
                    'last_login_date' => now() ,
                    'raw_pin'         => $pin_service->generateUniquePin() ,
                ] );
                $userArray = $tenantUser->toArray();

                activity()->on( $tenantUser )->log( 'Logged in via central app' );

                tenancy()->end();

                return new JsonResponse( [
                    'message' => trans( 'all.message.login_success' ) ,
                    'token'   => $token ,
                    'tenant'  => [
                        'id'     => $tenant->getTenantKey() ,
                        'domain' => $tenant->domains()->first()?->domain ,
                    ] ,
//                    'user'    => new UserResource( $tenantUser ) ,
                    'user'    => $userArray  ,
                ] , 200 );

            } catch ( \Exception $e ) {
                tenancy()->end(); // always clean up on failure
//                return new JsonResponse( [ 'errors' => [ 'validation' => $e->getMessage() ] ] , 422 );
                return new JsonResponse( [
                    'errors' => [
                        'validation' => $e->getMessage() ,
                        'file'       => $e->getFile() , // ADD THIS
                        'line'       => $e->getLine() , // ADD THIS
                    ]
                ] , 422 );
            }
        }

        public function logout(Request $request) : JsonResponse
        {
            $user = $request->user();
            if ( $user ) {
                $user->tokens()->delete();
            }

            Auth::guard( 'web' )->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ( $user ) {
                activity()->on( $user )->log( 'Logged out via central app' );
            }

            return new JsonResponse( [ 'message' => trans( 'all.message.logout_success' ) ] , 200 );
        }

        public function me(Request $request) : JsonResponse
        {
            return new JsonResponse( [ 'user' => new UserResource( $request->user() ) ] );
        }
    }