<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Status;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\MenuResource;
    use App\Http\Resources\PermissionResource;
    use App\Http\Resources\UserResource;
    use App\Libraries\AppLibrary;
    use App\Models\User;
    use App\Services\DefaultAccessService;
    use App\Services\MenuService;
    use App\Services\PermissionService;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;

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

        public function login(Request $request) : JsonResponse
        {
            // 1. Validation
            $isPinLogin = $request->filled( 'pin' );
            $rules      = $isPinLogin
                ? [ 'pin' => [ 'required' , 'string' , 'size:5' ] ]
                : [ 'email' => [ 'required' , 'string' ] , 'password' => [ 'required' , 'string' ] ];

            $validator = Validator::make( $request->all() , $rules );
            if ( $validator->fails() ) {
                return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
            }

            $user = NULL;

            // 2. Authentication Logic
            if ( $isPinLogin ) {
                // WARNING: This is slow. Consider a unique 'device_id' + 'pin' combo instead.
                $user = User::where( 'status' , Status::ACTIVE )
                            ->whereNotNull( 'pin' )
                            ->get() // Fetching all to check hash (still heavy)
                            ->first( fn($u) => Hash::check( $request->pin , $u->pin ) );
            }
            else {
                $loginField = filter_var( $request->email , FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';

                if ( Auth::attempt( [ $loginField => $request->email , 'password' => $request->password , 'status' => Status::ACTIVE ] ) ) {
                    $user = Auth::user();
                }
            }

            // 3. Validation of Credentials
            if ( ! $user ) {
                return new JsonResponse( [ 'errors' => [ 'validation' => trans( 'all.message.credentials_invalid' ) ] ] , 401 );
            }

            // 4. Role Check
            $role = $user->roles()->first();
            if ( ! $role ) {
                return new JsonResponse( [ 'errors' => [ 'validation' => trans( 'all.message.role_exist' ) ] ] , 400 );
            }

            // 5. Token Generation (The Sanctum way)
            // Revoke old tokens if you want to allow only one device
            $user->tokens()->delete();
            $token = $user->createToken( 'auth_token' )->plainTextToken;

            return new JsonResponse( [
                'message'           => trans( 'all.message.login_success' ) ,
                'token'             => $token ,
                'subscribed'        => $subscription->status ?? NULL ,
                'user'              => new UserResource( $user ) ,
                'menu'              => MenuResource::collection( collect( $this->menuService->menu( $role ) ) ) ,
                'permission'        => PermissionResource::collection( $this->permissionService->permission( $role ) ) ,
                'defaultPermission' => AppLibrary::defaultPermission( $permission ?? [] ) ,
            ] , 200 );
        }

        public function token(Request $request)
        {
            $user = $request->user();
            $user->tokens()->delete();
            $token = $request->user()->createToken( 'erudite' )->plainTextToken;
            return response()->json( [ 'token' => $token ] );
        }

        public function menu()
        {
            $user = auth()->user();
            return MenuResource::collection( collect( $this->menuService->menu( $user->roles[ 0 ] ) ) );
        }

        public function logout(Request $request) : JsonResponse
        {
            $request->user()->currentAccessToken()->delete();
            return new JsonResponse( [
                'message' => trans( 'all.message.logout_success' )
            ] , 200 );
        }
    }
