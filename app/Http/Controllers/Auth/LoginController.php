<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Status;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\MenuResource;
    use App\Http\Resources\UserResource;
    use App\Models\User;
    use App\Services\DefaultAccessService;
    use App\Services\MenuService;
    use App\Services\PermissionService;
    use App\Services\PinService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Validator;
    use Laravel\Sanctum\PersonalAccessToken;
    use Random\RandomException;

    class LoginController extends Controller
    {
        public string               $token;
        public DefaultAccessService $defaultAccessService;
        public PermissionService    $permissionService;
        public MenuService          $menuService;

        public function __construct(
            MenuService $menuService ,
            PermissionService $permissionService ,
            DefaultAccessService $defaultAccessService
        )
        {
            $this->menuService          = $menuService;
            $this->permissionService    = $permissionService;
            $this->defaultAccessService = $defaultAccessService;
        }

        /**
         * @throws RandomException
         */
        public function login(Request $request , PinService $pin_service) : JsonResponse
        {

            try {
                $isPinLogin = $request->filled( 'pin' );
                $pin        = $request->string( 'pin' );
                $rules      = $isPinLogin
                    ? [ 'pin' => [ 'required' , 'string' , 'size:5' ] ]
                    : [ 'email' => [ 'required' , 'string' ] , 'password' => [ 'required' , 'string' ] ];
                $validator  = Validator::make( $request->all() , $rules );
                if ( $validator->fails() ) {
                    return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
                }
                $user = NULL;
                if ( $isPinLogin ) {
                    $pin_hash = $pin_service->hashPin( $pin );
                    $user     = User::where( [ 'pin' => $pin_hash , 'status' => Status::ACTIVE ] )->first();
                    if ( $user && $pin_service->verifyPin( $user->pin , $pin ) ) {
                        Auth::login( $user , TRUE );
                    }
                    else {
                        $user = NULL;
                    }
                }
                else {
                    $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

                    // Auth::attempt automatically handles the session if successful
                    if ( Auth::attempt( [ $loginField => $request->email , 'password' => $request->password , 'status' => Status::ACTIVE ] , TRUE ) ) {
                        $user = Auth::user();
                    }
                }// 3. Validation of Credentials
                if ( ! $user ) {
                    return new JsonResponse( [ 'errors' => [ 'validation' => trans( 'all.message.credentials_invalid' ) ] ] , 401 );
                }
                if ( $request->hasSession() ) {
                    $request->session()->regenerate();
                }// 6. Token Generation
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
                }// Update last login date
                $user->update( [ 'last_login_date' => now() ] );
                $user->update( [ 'raw_pin' => $pin_service->generateUniquePin() ] );
                activity()
                    ->on( $user )
                    ->log( 'Logged in' );
                return new JsonResponse( [
                    'message' => trans( 'all.message.login_success' ) ,
                    'token'   => $token ,
                    'user'    => new UserResource( $user ) ,
                ] , 200 );
            } catch ( RandomException $e ) {
                throw new \Exception( $e->getMessage() , 422 );
            }
        }

        public function token(Request $request)
        {
            $token = '';
            $user  = $request->user();
            if ( $user ) {
                $user?->tokens()?->delete();
                $token = $request->user()->createToken( 'erudite' )->plainTextToken;
            }
            return response()->json( [ 'token' => $token ] );
        }

        public function menu()
        {
            $user = auth()->user();
            return MenuResource::collection( collect( $this->menuService->menu( $user->roles[ 0 ] ) ) );
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
            activity()
                ->on( $user )
                ->log( 'Logged out' );

            return new JsonResponse( [
                'message' => trans( 'all.message.logout_success' )
            ] , 200 );
        }

    }