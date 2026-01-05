<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Status;
    use App\Http\Controllers\Controller;
    use App\Http\Resources\MenuResource;
    use App\Http\Resources\PermissionResource;
    use App\Http\Resources\UserResource;
    use App\Libraries\AppLibrary;
    use App\Models\Subscription;
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

        public function login(Request $request): JsonResponse
        {
            $isPinLogin = $request->filled('pin');

            /**
             * STEP 1: Validate request based on mode
             */
            $validator = Validator::make($request->all(), $isPinLogin
                ? ['pin' => ['required', 'string', 'size:5']]
                : [
                    'email'    => ['required', 'string', 'max:255'],
                    'password' => ['required', 'string', 'min:2'],
                ]
            );

            if ($validator->fails()) {
                return new JsonResponse(['errors' => $validator->errors()], 422);
            }

            $user = null;

            /**
             * STEP 2: Handle PIN Login
             */
            if ($isPinLogin) {
                // Since PINs are hashed and unique, we look through active users.
                // We use lazy loading (cursor) to keep memory usage low.
                $user = User::where('status', Status::ACTIVE)
                            ->whereNotNull('pin')
                            ->cursor()
                            ->first(fn ($u) => Hash::check($request->pin, $u->pin));

                if (!$user) {
                    return new JsonResponse([
                        'errors' => ['validation' => trans('all.message.credentials_invalid')],
                    ], 401);
                }

                Auth::guard('web')->login($user);
            }
            /**
             * STEP 3: Handle Password Login
             */
            else {
                $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

                $credentials = [
                    $loginField => $request->email,
                    'password'  => $request->password,
                    'status'    => Status::ACTIVE,
                ];

                if (!Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
                    return new JsonResponse([
                        'errors' => ['validation' => trans('all.message.credentials_invalid')],
                    ], 401);
                }

                $user = Auth::user();
            }

            /**
             * STEP 4: Finalize Login (Roles, Tokens, Resources)
             */
            if (!$user->roles()->exists()) {
                return new JsonResponse([
                    'errors' => ['validation' => trans('all.message.role_exist')],
                ], 400);
            }

            $token = $user->createToken('auth_token')->plainTextToken;
            $role  = $user->roles[0];

            return new JsonResponse([
                'message'           => trans('all.message.login_success'),
                'token'             => $token,
                'subscribed'        => $subscription->status ?? null,
                'user'              => new UserResource($user),
                'menu'              => MenuResource::collection(collect($this->menuService->menu($role))),
                'permission'        => PermissionResource::collection($this->permissionService->permission($role)),
                'defaultPermission' => AppLibrary::defaultPermission($permission ?? []),
            ], 200);
        }


        /**
         * @throws \Exception
         */
//        public function login(Request $request) : JsonResponse
//        {
//            $validator = Validator::make(
//                $request->all() ,
//                [
//                    'email'    => $request[ 'phone' ] ? [ 'nullable' , 'string' , 'email' , 'max:255' ] : [ 'required' , 'string' , 'max:255' ] ,
//                    'phone'    => $request[ 'email' ] ? [ 'nullable' , 'string' , 'max:20' ] : [ 'required' , 'string' , 'max:20' ] ,
//                    'password' => [ 'required' , 'string' , 'min:6' ] ,
//                ] ,
//            );
//
//            if ( $validator->fails() ) {
//                if ( ! $request[ 'email' ] && ! $request[ 'phone' ] ) {
//                    return new JsonResponse( [
//                        'errors' => [
//                                'email_or_phone' => trans( 'all.message.email_or_phone_required' ) ,
//                            ] + $validator->errors()->toArray()
//                    ] , 422 );
//                }
//                else {
//                    return new JsonResponse( [
//                        'errors' => $validator->errors()
//                    ] , 422 );
//                }
//            }
//
//            $request->merge( [ 'status' => Status::ACTIVE ] );
//            $is_email = Validator::make(
//                [ 'email' => $request->input( 'email' ) ] ,
//                [ 'email' => 'required|email' ]
//            )->passes();
//
//            if ( $request[ 'email' ] && $is_email ) {
//                if ( ! Auth::guard( 'web' )->attempt( $request->only( 'email' , 'password' , 'status' ) ) ) {
//                    return new JsonResponse( [
//                        'errors' => [ 'validation' => trans( 'all.message.credentials_invalid' ) ]
//                    ] , 400 );
//                }
//                $user = User::where( 'email' , $request[ 'email' ] )->first();
//            }
//            else {
//                if ( ! Auth::guard( 'web' )->attempt( $request->only( 'country_code' , 'phone' , 'password' , 'status' ) ) ) {
//                    return new JsonResponse( [
//                        'errors' => [ 'validation' => trans( 'all.message.credentials_invalid' ) ]
//                    ] , 400 );
//                }
//                $user = User::where( [ 'phone' => $request[ 'phone' ] , 'country_code' => $request->country_code ] )->first();
//            }
//
//            $this->token = $user->createToken( 'auth_token' )->plainTextToken;
//
//            if ( ! isset( $user->roles[ 0 ] ) ) {
//                return new JsonResponse( [
//                    'errors' => [ 'validation' => trans( 'all.message.role_exist' ) ]
//                ] , 400 );
//            }
//
//            $permission        = PermissionResource::collection( $this->permissionService->permission( $user->roles[ 0 ] ) );
//            $defaultPermission = AppLibrary::defaultPermission( $permission );
//
//            $subscription = Subscription::latest()->first();
//            return new JsonResponse( [
//                'message'           => trans( 'all.message.login_success' ) ,
//                'token'             => $this->token ,
//                'subscribed'        => $subscription->status ?? NULL ,
//                'user'              => new UserResource( $user ) ,
//                'menu'              => MenuResource::collection( collect( $this->menuService->menu( $user->roles[ 0 ] ) ) ) ,
//                'permission'        => $permission ,
//                'defaultPermission' => $defaultPermission ,
////                'defaultPermission' => null ,
//            ] , 201 );
//        }

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
