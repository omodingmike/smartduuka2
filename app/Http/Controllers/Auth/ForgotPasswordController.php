<?php

    namespace App\Http\Controllers\Auth;

    use App\Enums\Activity;
    use App\Http\Controllers\Controller;
    use App\Http\Requests\SignupEmailRequest;
    use App\Http\Requests\SignupPhoneRequest;
    use App\Http\Requests\VerifyPhoneRequest;
    use App\Models\User;
    use App\Services\MenuService;
    use App\Services\OtpManagerService;
    use App\Services\PermissionService;
    use App\Traits\ApiResponse;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;
    use Smartisan\Settings\Facades\Settings;

    class ForgotPasswordController extends Controller
    {
        use ApiResponse;

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
                'identifier' => [ 'required' , 'string' , 'max:255' ] ,
            ] );

            if ( $validator->fails() ) {
                return $this->error( $validator->errors()->first() , 422 );
            }

            $identifier = $request->input( 'identifier' );

            // 1. Determine if input is email or phone
            $isEmail = filter_var( $identifier , FILTER_VALIDATE_EMAIL );

            if ( $isEmail ) {
                $user = User::where( 'email' , $identifier )->first();
                if ( ! $user ) {
                    return $this->error( trans( 'all.message.email_does_not_exist' ) , 422 );
                }

                try {
                    if ( Settings::group( 'site' )->get( 'site_email_verification' ) == Activity::ENABLE ) {
                        $request->merge( [ 'email' => $user->email ] );
                        $this->otpManagerService->resetOtpEmail( $request );
                        return $this->success( trans( 'all.message.check_your_email_for_code' ) );
                    }
                    return $this->success( trans( 'all.message.user_verify_success' ) );
                } catch ( Exception $exception ) {
                    return $this->error( $exception->getMessage() , 422 );
                }

            }
            else {
                // 2. Normalize Ugandan Phone Number
                // Remove spaces/special chars and ensure it's in a consistent format (e.g., 07...)
                $phone = preg_replace( '/[^0-9]/' , '' , $identifier );

                // If it starts with 256, convert to 0 for database consistency
                if ( str_starts_with( $phone , '256' ) ) {
                    $phone = '0' . substr( $phone , 3 );
                }

                $user = User::where( 'phone' , $phone )->first();
                if ( ! $user ) {
                    return $this->error( trans( 'all.message.phone_does_not_exist' ) , 422 );
                }

                try {
//                    if ( Settings::group( 'site' )->get( 'site_phone_verification' ) == Activity::ENABLE ) {
//                        $request->merge( [ 'phone' => $user->phone , 'country_code' => '256' ] );
//                        $this->otpManagerService->otpPhone( $request );
//                        return $this->success( trans( 'all.message.check_your_phone_for_code' ) );
//                    }
                    $request->merge( [ 'phone' => $user->phone , 'country_code' => '256' ] );
                    $this->otpManagerService->otpPhone( $request );
                    return $this->success( trans( 'all.message.check_your_phone_for_code' ) );
//                    return $this->success( trans( 'all.message.user_verify_success' ) );
                } catch ( Exception $exception ) {
                    return $this->error( $exception->getMessage() , $exception->getCode() );
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

        public function verifyEmail(Request $request) : JsonResponse
        {
            try {
                $isValid = $this->otpManagerService->verifyEmail( $request );
                if ( $isValid ) {
                    return $this->success( trans( 'all.message.otp_verify_success' ) );
                }
                else {
                    return $this->error( trans( 'all.message.code_is_invalid' ) , 422 );
                }
            } catch ( Exception $exception ) {
                return $this->error( $exception->getMessage() , $exception->getCode() );
            }
        }

        public function resetPassword(Request $request)
        {
            $validator = Validator::make( $request->all() , [
                'identifier'            => 'required|string|max:255' ,
                'password'              => 'required|string|min:6|confirmed' ,
                'password_confirmation' => 'required|string|min:6' ,
            ] );

            if ( $validator->fails() ) {
                // Return the first validation error message
                return $this->error( $validator->errors()->first() , 422 );
            }

            $identifier = $request->input( 'identifier' );
            $user       = NULL;

            // 1. Check if identifier is an Email
            if ( filter_var( $identifier , FILTER_VALIDATE_EMAIL ) ) {
                $user = User::where( 'email' , $identifier )->first();
            }
            // 2. Otherwise, treat it as a Phone Number
            else {
                // Normalize Ugandan Phone (remove non-digits, convert 256 to 0)
                $phone = preg_replace( '/[^0-9]/' , '' , $identifier );
                if ( str_starts_with( $phone , '256' ) ) {
                    $phone = '0' . substr( $phone , 3 );
                }

                // Search by phone (ignoring country_code column if you store full normalized numbers)
                $user = User::where( 'phone' , $phone )->first();
            }

            // 3. Update Password if user exists
            if ( $user ) {
                $user->update( [
                    'password' => Hash::make( $request->post( 'password' ) )
                ] );

                return $this->success( trans( 'all.message.reset_successfully' ) );
            }

            // 4. Return error if user not found
            return $this->error( trans( 'all.message.user_does_not_exist' ) , 422 );
        }

        public function resetPin(Request $request)
        {
            $validator = Validator::make( $request->all() , [
                'identifier' => 'required|string|max:255' ,
                'pin'        => 'required|string|digits:5' ,
            ] );

            if ( $validator->fails() ) {
                return $this->error( $validator->errors()->first() , 422 );
            }

            $identifier = $request->input( 'identifier' );

            // Identify User by Email or Phone
            if ( filter_var( $identifier , FILTER_VALIDATE_EMAIL ) ) {
                $user = User::where( 'email' , $identifier )->first();
            }
            else {
                // Normalize Ugandan Phone (converts +256... or 256... to 0...)
                $phone = preg_replace( '/[^0-9]/' , '' , $identifier );
                if ( str_starts_with( $phone , '256' ) ) {
                    $phone = '0' . substr( $phone , 3 );
                }
                $user = User::where( 'phone' , $phone )->first();
            }

            if ( $user ) {
                $user->update( [
                    'pin' => Hash::make( $request->post( 'pin' ) )
                ] );
                return $this->success(
                    trans( 'all.message.pin_reset_successfully' ) );
            }

            return $this->error( trans( 'all.message.user_does_not_exist' ) , 422 );
        }
    }
