<?php

    namespace App\Services;

    use App\Enums\OtpType;
    use App\Events\SendSmsCode;
    use App\Events\SendVerifyEmailCode;
    use App\Helpers\SMS;
    use App\Http\Requests\VerifyEmailRequest;
    use App\Http\Requests\VerifyPhoneRequest;
    use App\Jobs\SendEmailCodeNotificationJob;
    use App\Jobs\SendSmsCodeJob;
    use App\Models\Otp;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;

    class OtpManagerService
    {
        use SMS;

        /**
         * @throws Exception
         */

        public function otpPhone(Request $request) : bool
        {
            try {
                // Clear existing OTPs for this phone to prevent collisions
                DB::table('otps')->where([
                    ['phone', $request->post('phone')],
                    ['code', '256'], // Fixed to match your '256' storage logic
                ])->delete();

                // Determine digit limit (Defaulting to 6)
                $digitLimit = (int) Settings::group('otp')->get('otp_digit_limit', 6);

                // If the setting is missing or logic requires forced 6 digits:
                $digitLimit = 6;

                // Generate 6-digit token (e.g., 100000 to 999999)
                $token = random_int(100000, 999999);

                $otp = Otp::create([
                    'phone'      => $request->phone,
                    'code'       => '256',
                    'token'      => (string) $token, // Ensure it's stored as string
                    'created_at' => now(),
                ]);

                if (!blank($otp)) {
                    SendSmsCodeJob::dispatch([
                        'phone' => $request->post('phone'),
                        'code'  => '256',
                        'token' => $token
                    ]);
                }

                return TRUE;
            } catch (Exception $exception) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage(), 422);
            }
        }

        public function resetOtpEmail(Request $request) : bool
        {
            try {
                $otp = DB::table( 'password_reset_tokens' )->where( [
                    [ 'email' , $request->post( 'email' ) ]
                ] );

                if ( $otp->exists() ) {
                    $otp->delete();
                }

                if ( OtpType::EMAIL == Settings::group( 'otp' )->get( 'otp_type' ) || OtpType::BOTH == Settings::group( 'otp' )->get(
                        'otp_type'
                    ) ) {
                    $token = rand(
                        pow( 10 , (int) Settings::group( 'otp' )->get( 'otp_digit_limit' ) - 1 ) ,
                        pow( 10 , (int) Settings::group( 'otp' )->get( 'otp_digit_limit' ) ) - 1
                    );
                }
                else {
                    $token = rand( pow( 10 , 4 - 1 ) , pow( 10 , 4 ) - 1 );
                }
                // secure, always 6 characters long (pads with leading zeros if needed)
                $token = str_pad( (string) random_int( 0 , 999999 ) , 6 , '0' , STR_PAD_LEFT );

                $password_reset = DB::table( 'password_reset_tokens' )->insert( [
                    'email'      => $request->post( 'email' ) ,
                    'token'      => $token ,
                    'created_at' => Carbon::now()
                ] );

                if ( ! blank( $password_reset ) ) {
                    SendEmailCodeNotificationJob::dispatch( [ 'email' => $request->post( 'email' ) , 'pin' => $token ] );
                }

                return TRUE;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function otpEmail(Request $request) : bool
        {
            try {
                if ( config( 'system.demo' ) == "True" || config( 'system.demo' ) == "TRUE" || config( 'system.demo' ) == "true" || config( 'system.demo' ) == 1 ) {
                    return TRUE;
                }
                $otp = DB::table( 'password_reset_tokens' )->where( [
                    [ 'email' , $request->post( 'email' ) ]
                ] );

                if ( $otp->exists() ) {
                    $otp->delete();
                }

                if ( OtpType::EMAIL == Settings::group( 'otp' )->get( 'otp_type' ) || OtpType::BOTH == Settings::group( 'otp' )->get(
                        'otp_type'
                    ) ) {
                    $token = rand(
                        pow( 10 , (int) Settings::group( 'otp' )->get( 'otp_digit_limit' ) - 1 ) ,
                        pow( 10 , (int) Settings::group( 'otp' )->get( 'otp_digit_limit' ) ) - 1
                    );
                }
                else {
                    $token = rand( pow( 10 , 4 - 1 ) , pow( 10 , 4 ) - 1 );
                }

                $password_reset = DB::table( 'password_reset_tokens' )->insert( [
                    'email'      => $request->post( 'email' ) ,
                    'token'      => $token ,
                    'created_at' => Carbon::now()
                ] );

                if ( ! blank( $password_reset ) ) {
                    SendVerifyEmailCode::dispatch( [ 'email' => $request->post( 'email' ) , 'pin' => $token ] );
                }

                return TRUE;
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        /**
         * @throws Exception
         */
        public function verifyPhone(VerifyPhoneRequest $request) : bool
        {
            try {
                if ( config( 'system.demo' ) == "True" || config( 'system.demo' ) == "TRUE" || config( 'system.demo' ) == "true" || config( 'system.demo' ) == 1 ) {
                    return TRUE;
                }

                $otp = DB::table( 'otps' )->where( [
                    [ 'phone' , $request->post( 'phone' ) ] ,
                    [ 'token' , $request->post( 'token' ) ] ,
                ] );
                if ( $otp->exists() ) {
                    $difference = Carbon::now()->diffInSeconds( $otp->first()->created_at );
                    if ( $difference > (int) Settings::group( 'otp' )->get( 'otp_expire_time' ) * 60 ) {
                        throw new Exception( trans( 'all.message.code_is_expired' ) , 422 );
                    }
                    else {
                        $otp->delete();
                        return TRUE;
                    }
                }
                else {
                    throw new Exception( trans( 'all.message.code_is_invalid' ) , 422 );
                }
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function verifyEmail(Request $request) : bool
        {
            try {
                $identifier = $request->post( 'identifier' );
                $token      = $request->post( 'token' );

                // 1. Determine if the identifier is an email
                $isEmail = filter_var( $identifier , FILTER_VALIDATE_EMAIL );

                if ( $isEmail ) {
                    // Check standard Laravel password reset table
                    $verify = DB::table( 'password_reset_tokens' )->where( [
                        [ 'email' , $identifier ] ,
                        [ 'token' , $token ] ,
                    ] );
                }
                else {
                    // 2. Normalize Phone (Ugandan format)
                    $phone = preg_replace( '/[^0-9]/' , '' , $identifier );
                    if ( str_starts_with( $phone , '256' ) ) {
                        $phone = '0' . substr( $phone , 3 );
                    }

                    // Check your custom OTPS table for phone verification
                    $verify = DB::table( 'otps' )->where( [
                        [ 'phone' , $phone ] ,
                        [ 'token' , $token ] ,
                    ] );
                }

                if ( $verify->exists() ) {
                    $verify->delete();
                    return TRUE;
                }

                return FALSE;

            } catch ( Exception $exception ) {
                Log::error( 'Verification Error: ' . $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }
    }
