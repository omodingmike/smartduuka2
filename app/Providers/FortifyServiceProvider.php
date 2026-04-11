<?php

    namespace App\Providers;

    use App\Actions\Fortify\CreateNewUser;
    use App\Actions\Fortify\ResetUserPassword;
    use App\Actions\Fortify\UpdateUserPassword;
    use App\Actions\Fortify\UpdateUserProfileInformation;
    use App\Enums\Status;
    use App\Models\CentralUser;
    use App\Models\User;
    use App\Services\PinService;
    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\RateLimiter;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\ServiceProvider;
    use Illuminate\Support\Str;
    use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
    use Laravel\Fortify\Contracts\LoginResponse;
    use Laravel\Fortify\Fortify;
    use Laravel\Sanctum\PersonalAccessToken;

    class FortifyServiceProvider extends ServiceProvider
    {
        public function register() : void
        {
            $this->app->instance( LoginResponse::class , new class implements LoginResponse {
                public function toResponse($request) : JsonResponse
                {
                    $user = $request->user();
                    return response()->json( [
                        'two_factor' => FALSE ,
                        'token'      => $user->generated_token ,
                        'user'       => $user->resolved_data ,
                        'tenant_id'  => $user->tenant_id
                    ] );
                }
            } );
        }

        public function boot(PinService $pin_service) : void
        {
            Fortify::createUsersUsing( CreateNewUser::class );
            Fortify::updateUserProfileInformationUsing( UpdateUserProfileInformation::class );
            Fortify::updateUserPasswordsUsing( UpdateUserPassword::class );
            Fortify::resetUserPasswordsUsing( ResetUserPassword::class );
            Fortify::redirectUserForTwoFactorAuthenticationUsing( RedirectIfTwoFactorAuthenticatable::class );

            RateLimiter::for( 'login' , function (Request $request) {
                $throttleKey = Str::transliterate( Str::lower( $request->input( Fortify::username() ) ) . '|' . $request->ip() );

                return Limit::perMinute( 5 )->by( $throttleKey );
            } );

            RateLimiter::for( 'two-factor' , function (Request $request) {
                return Limit::perMinute( 5 )->by( $request->session()->get( 'login.id' ) );
            } );

            Fortify::authenticateUsing( function (Request $request) use ($pin_service) {
                try {
                    $isPinLogin = $request->filled( 'pin' );
                    $pin        = $request->string( 'pin' );
                    $rules      = $isPinLogin
                        ? [ 'pin' => [ 'required' , 'string' , 'size:5' ] ]
                        : [ 'email' => [ 'required' , 'string' ] , 'password' => [ 'required' , 'string' ] ];

                    $validator = Validator::make( $request->all() , $rules );
                    if ( $validator->fails() ) {
                        throw  new \Exception( $validator->errors()->first() , 422 );
                    }
                    $centralUser = NULL;
                    // 1. Authenticate against central DB
                    if ( $isPinLogin ) {
                        $pin_hash    = $pin_service->hashPin( $pin );
                        $centralUser = CentralUser::where( 'pin' , $pin_hash )->first();
                        if ( ! $centralUser || ! $pin_service->verifyPin( $centralUser->pin , $pin ) ) {
                            $centralUser = NULL;
                            throw  new \Exception( trans( 'all.message.credentials_invalid' ) , 422 );
                        }
                    }
                    else {
                        $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';
//                        $centralUser = CentralUser::where( $loginField , $request->email )
//                                                  ->where( 'status' , Status::ACTIVE )
//                                                  ->first();

                        if ( Auth::attempt( [ $loginField => $request->email , 'password' => $request->password , 'status' => Status::ACTIVE ] , TRUE ) ) {
                            $centralUser = Auth::user();
                        }
                        else {
                            throw  new \Exception( trans( 'all.message.credentials_invalid' ) , 422 );
                        }

//                        if ( ! $centralUser || ! Hash::check( $request->password , $centralUser->password ) ) {
//                            $centralUser = NULL;
//                        }
                    }

                    if ( ! $centralUser ) {
                        throw  new \Exception( trans( 'all.message.credentials_invalid' ) , 422 );
                    }

                    // 2. Resolve tenant from central user
                    $tenant = $centralUser->tenants()->first();

                    if ( ! $tenant ) {
                        throw  new \Exception( 'No tenant associated with this account' , 422 );
                    }

                    if ( ! $tenant->database() ) {
                        throw  new \Exception( 'Tenant database not configured' , 422 );
                    }

                    tenancy()->initialize( $tenant->id );

                    $tenantUser = User::where(
                        $centralUser->getGlobalIdentifierKeyName() ,
                        $centralUser->getGlobalIdentifierKey()
                    )->first();

                    if ( ! $tenantUser ) {
                        tenancy()->end();
                        throw  new \Exception( 'User not found in tenant' , 422 );
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
                    $tenantUser->generated_token = $token;
                    $tenantUser->resolved_data   = $userArray;
                    $tenantUser->tenant_id       = $tenant->getTenantKey();
                    tenancy()->end();
                    return $tenantUser;

                } catch ( \Exception $e ) {
                    tenancy()->end();
                    return NULL;
//                    throw  new \Exception( $e->getMessage() , 422 );
                }
            } );
        }
    }
