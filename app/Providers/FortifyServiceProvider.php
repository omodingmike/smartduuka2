<?php

    namespace App\Providers;

    use App\Actions\Fortify\CreateNewUser;
    use App\Actions\Fortify\ResetUserPassword;
    use App\Actions\Fortify\SyncTenantUsersToCentral;
    use App\Actions\Fortify\UpdateUserPassword;
    use App\Actions\Fortify\UpdateUserProfileInformation;
    use App\Enums\AppID;
    use App\Enums\Role;
    use App\Enums\Status;
    use App\Models\CentralUser;
    use App\Models\User;
    use App\Services\PinService;
    use Illuminate\Cache\RateLimiting\Limit;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
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
                    $user      = $request->user();
                    $tenant_id = $user->tenant_id;

                    $token = $user->web_token;
                    if ( $token ) {
                        $accessToken = PersonalAccessToken::findToken( $token );
                        if ( ! $accessToken ) {
                            $token = NULL;
                        }
                    }

                    if ( ! $token ) {
                        $token = $user->createToken( 'auth_token' )->plainTextToken;
                        $user->update( [ 'web_token' => $token ] );
                    }

                    return response()->json( [
                        'two_factor' => FALSE ,
                        'token'      => $token ,
                        'user'       => $user->toArray() ,
                        'tenant_id'  => $tenant_id ,
                    ] );
                }
            } );
        }

        public function boot(PinService $pinService) : void
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

            Fortify::authenticateUsing( function (Request $request) use ($pinService) {
                $centralUser = NULL;

                if ( $request->filled( 'pin' ) ) {
                    $validator = Validator::make( $request->only( 'pin' ) , [ 'pin' => 'required|string|size:5' ] );

                    if ( $validator->fails() ) {
                        return NULL;
                    }

                    $centralUser = CentralUser::where( 'pin' , $pinService->hashPin( $request->string( 'pin' ) ) )?->first();
                }
                else {
                    $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

                    $validator = Validator::make( $request->all() , [
                        $loginField => 'required|string' ,
                        'password'  => 'required|string' ,
                    ] );

                    if ( $validator->fails() ) {
                        return NULL;
                    }

                    $user = CentralUser::where( $loginField , $request->email )
                                       ->where( 'status' , Status::ACTIVE )?->first();

                    if ( $user && Hash::check( $request->password , $user->password ) ) {
                        $centralUser = $user;
                    }
                }

                if ( ! $centralUser ) {
                    return NULL;
                }

                $tenant = $centralUser->tenants()?->first();

                if ( ! $tenant || ! $tenant->database() ) {
                    return NULL;
                }

                tenancy()->initialize( $tenant );
                $app_id     = $request->header( 'X-App-Id' );
                $tenantUser = User::where(
                    $centralUser->getGlobalIdentifierKeyName() ,
                    $centralUser->getGlobalIdentifierKey()
                )->when( $app_id == AppID::CASHFLOW , function ($query) {
                    $query->role( Role::ADMIN );
                } )->first();

                if ( $tenantUser ) {
                    if ( tenancy()->initialized ) {
                        $tenantUser->withoutEvents( function () use ($tenantUser , $tenant) {
                            $tenantUser->update( [
                                'last_login_date' => now() ,
                                'tenant_id'       => $tenant->id ,
                                'raw_pin'         => NULL
                            ] );
                        } );
                        activityLog( 'Logged' , $app_id , $tenantUser );
                        app( SyncTenantUsersToCentral::class )->sync();
                    }
                    return $tenantUser;
                }
                return NULL;
            } );
        }
    }
