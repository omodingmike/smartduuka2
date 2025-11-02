<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Activity;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\SignupEmailRequest;
    use App\Http\Requests\SignupPhoneRequest;
    use App\Http\Requests\VerifyEmailRequest;
    use App\Http\Requests\VerifyPhoneRequest;
    use App\Http\Resources\MenuResource;
    use App\Http\Resources\PermissionResource;
    use App\Http\Resources\UserResource;
    use App\Libraries\AppLibrary;
    use App\Models\User;
    use App\Services\MenuService;
    use App\Services\OtpManagerService;
    use App\Services\PermissionService;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Smartisan\Settings\Facades\Settings;

    class ForgotPasswordController extends Controller
    {

        public int                $pin;
        public string             $token;
        private OtpManagerService $otpManagerService;
        public PermissionService  $permissionService;
        public MenuService        $menuService;

        public function __construct(OtpManagerService $otpManagerService , PermissionService $permissionService , MenuService $menuService)
        {
            $this->otpManagerService = $otpManagerService;
            $this->permissionService = $permissionService;
            $this->menuService       = $menuService;
        }

        public function forgotPassword(Request $request)
        {
            $validator = Validator::make( $request->all() , [
                'email'        => request( 'phone' ) ? [ 'nullable' , 'string' , 'email' , 'max:255' ] : [ 'required' , 'string' , 'email' , 'max:255' ] ,
                'phone'        => request( 'email' ) ? [ 'nullable' , 'string' , 'max:20' ] : [ 'required' , 'string' , 'max:20' ] ,
                'country_code' => request( 'email' ) ? [ 'nullable' , 'string' , 'max:10' ] : [ 'required' , 'string' , 'max:10' ] ,
            ] );

            if ( $validator->fails() ) {
                return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
            }

            $verifyEmail = User::where( 'email' , $request->post( 'email' ) )->exists();
            $verifyPhone = User::where( [ 'phone' => $request->post( 'phone' ) , 'country_code' => $request->post( 'country_code' ) ] )->exists();

            if ( $verifyEmail && $request->post( 'email' ) ) {
                try {
                    if ( Settings::group( 'site' )->get( 'site_email_verification' ) == Activity::ENABLE && $request->post( 'email' ) ) {
                        $this->otpManagerService->resetOtpEmail( $request );
                        return response( [ 'status' => TRUE , 'email' => $request->post( 'email' ) , 'message' => trans( "all.message.check_your_email_for_code" ) ] );
                    }
                    else {
                        return response( [ 'status' => TRUE , 'message' => trans( "all.message.user_verify_success" ) ] );
                    }
                } catch ( Exception $exception ) {
                    return response( [ 'status' => FALSE , 'message' => [ trans( 'all.message.token_created_fail' ) ] ] , 422 );
                }
            }
            else if ( $verifyPhone && $request->post( 'phone' ) ) {
                try {
                    if ( Settings::group( 'site' )->get( 'site_phone_verification' ) == Activity::ENABLE && $request->post( 'country_code' ) && $request->post( 'phone' ) ) {
                        $this->otpManagerService->otpPhone( $request );
                        return response(
                            [ 'status'  => TRUE , 'email' => $request->post( 'email' ) ,
                              'message' => trans( "all.message.check_your_phone_for_code" )
                            ] );
                    }
                    else {
                        return response( [ 'status' => TRUE , 'message' => trans( "all.message.user_verify_success" ) ] );
                    }
                } catch ( Exception $exception ) {
                    return response( [ 'status' => FALSE , 'message' => [ trans( 'all.message.token_created_fail' ) ] ] , 422 );
                }
            }
            else {
                if ( $request->post( 'email' ) ) {
                    return new JsonResponse( [
                        'errors' => [ 'email' => [ trans( 'all.message.email_does_not_exist' ) ] ]
                    ] , 422 );
                }
                else {
                    return new JsonResponse( [
                        'errors' => [ 'phone' => [ trans( 'all.message.phone_does_not_exist' ) ] ]
                    ] , 422 );
                }
            }
        }

        public function verifyCode(Request $request) : JsonResponse
        {
            $validator = Validator::make( $request->all() , [
                'email' => [ 'required' , 'string' , 'email' , 'max:255' ] ,
                'code'  => [ 'required' ] ,
            ] );

            if ( $validator->fails() ) {
                return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
            }

            $check = DB::table( 'password_resets' )->where( [
                [ 'email' , $request->post( 'email' ) ] ,
                [ 'token' , $request->post( 'code' ) ] ,
            ] );

            if ( $check->exists() ) {
                $difference = Carbon::now()->diffInSeconds( $check->first()->created_at );

                if ( $difference > (int) Settings::group( 'otp' )->get( 'otp_expire_time' ) * 60 ) {
                    return new JsonResponse( [
                        'errors' => [ 'code' => [ trans( 'all.message.code_is_expired' ) ] ]
                    ] , 400 );
                }

                $check->delete();

                return new JsonResponse( [
                    'message' => trans( 'all.message.you_can_reset_your_password' )
                ] , 200 );
            }
            else {
                return new JsonResponse( [
                    'errors' => [ 'code' => [ trans( 'all.message.code_is_invalid' ) ] ]
                ] , 400 );
            }
        }

        public function otpPhone(
            SignupPhoneRequest $request
        ) : Response | Application | ResponseFactory
        {
            try {
                $this->otpManagerService->otpPhone( $request );
                return response( [ 'status' => TRUE , 'message' => trans( "all.message.check_your_phone_for_code" ) ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function otpEmail(
            SignupEmailRequest $request
        ) : Response | Application | ResponseFactory
        {
            try {
                $this->otpManagerService->resetOtpEmail( $request );
                return response( [ 'status' => TRUE , 'message' => trans( "all.message.check_your_email_for_code" ) ] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function verifyPhone(
            VerifyPhoneRequest $request
        ) : JsonResponse
        {
            try {
                $this->otpManagerService->verifyPhone( $request );
                return new JsonResponse( [
                    'status'  => TRUE ,
                    'message' => trans( 'all.message.otp_verify_success' )
                ] , 200 );
            } catch ( Exception $exception ) {
                return new JsonResponse( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function verifyEmail(
            VerifyEmailRequest $request
        ) : JsonResponse
        {
            try {
                $this->otpManagerService->verifyEmail( $request );
                return new JsonResponse( [
                    'status'  => TRUE ,
                    'message' => trans( 'all.message.otp_verify_success' )
                ] , 200 );
            } catch ( Exception $exception ) {
                return new JsonResponse( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function resetPassword(Request $request) : JsonResponse
        {
            $validator = Validator::make( $request->all() , [
                'email'                 => request( 'phone' ) ? 'nullable|string|email|max:255' : 'required|string|email|max:255' ,
                'phone'                 => request( 'email' ) ? 'nullable|string|max:20' : 'required|string|max:20' ,
                'country_code'          => request( 'email' ) ? 'nullable|string|max:10' : 'required|string|max:10' ,
                'password'              => 'required|string|min:6|confirmed' ,
                'password_confirmation' => 'required|string|min:6' ,
            ] );


            if ( $validator->fails() ) {
                return new JsonResponse( [ 'errors' => $validator->errors() ] , 422 );
            }

            $userCheckByEmail = User::where( 'email' , $request->post( 'email' ) )->first();
            $userCheckByPhone = User::where( [ 'phone' => $request->post( 'phone' ) , 'country_code' => $request->post( 'country_code' ) ] )->first();

            if ( $userCheckByEmail && $request->post( 'email' ) ) {
                $userCheckByEmail->update( [
                    'password' => Hash::make( $request->post( 'password' ) )
                ] );

                Auth::guard( 'web' )->loginUsingId( $userCheckByEmail->id );
                $this->token       = $userCheckByEmail->createToken( 'auth_token' )->plainTextToken;
                $permission        = PermissionResource::collection( $this->permissionService->permission( $userCheckByEmail->roles[ 0 ] ) );
                $defaultPermission = AppLibrary::defaultPermission( $permission );
                return new JsonResponse( [
                    'status'            => TRUE ,
                    'message'           => trans( "all.message.reset_successfully" ) ,
                    'token'             => $this->token ,
                    'user'              => new UserResource( $userCheckByEmail ) ,
                    'menu'              => MenuResource::collection( collect( $this->menuService->menu( $userCheckByEmail->roles[ 0 ] ) ) ) ,
                    'permission'        => $permission ,
                    'defaultPermission' => $defaultPermission ,
                ] , 201 );
            }
            else if ( $userCheckByPhone && $request->post( 'phone' ) && $request->post( 'country_code' ) ) {
                $userCheckByPhone->update( [
                    'password' => Hash::make( $request->post( 'password' ) )
                ] );
                Auth::guard( 'web' )->loginUsingId( $userCheckByPhone->id );
                $this->token       = $userCheckByPhone->createToken( 'auth_token' )->plainTextToken;
                $permission        = PermissionResource::collection( $this->permissionService->permission( $userCheckByPhone->roles[ 0 ] ) );
                $defaultPermission = AppLibrary::defaultPermission( $permission );
                return new JsonResponse( [
                    'status'            => TRUE ,
                    'message'           => trans( "all.message.reset_successfully" ) ,
                    'token'             => $this->token ,
                    'user'              => new UserResource( $userCheckByPhone ) ,
                    'menu'              => MenuResource::collection( collect( $this->menuService->menu( $userCheckByPhone->roles[ 0 ] ) ) ) ,
                    'permission'        => $permission ,
                    'defaultPermission' => $defaultPermission ,
                ] , 201 );
            }
            else {
                return new JsonResponse( [
                    'errors' => [ 'message' => [ trans( 'all.message.user_does_not_exist' ) ] ]
                ] , 422 );
            }
        }
    }
