<?php

namespace App\Services;

use App\Jobs\SendEmailCodeNotificationJob;
use Exception;
use Carbon\Carbon;
use App\Models\Otp;
use App\Enums\OtpType;
use App\Events\SendSmsCode;
use Illuminate\Http\Request;
use App\Events\SendEmailCode;
use Illuminate\Support\Facades\DB;
use App\Events\SendVerifyEmailCode;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Requests\VerifyPhoneRequest;

class OtpManagerService
{

    /**
     * @throws Exception
     */
    public function otpPhone(Request $request): bool
    {
        try {
            if (config('system.demo') == "True" || config('system.demo') == "TRUE" || config('system.demo') == "true" || config('system.demo') == 1) {
                return true;
            }
            $otp = DB::table('otps')->where([
                ['phone', $request->post('phone')],
                ['code', $request->post('country_code')],
            ]);

            if ($otp->exists()) {
                $otp->delete();
            }

            if (OtpType::SMS == Settings::group('otp')->get('otp_type') || OtpType::BOTH == Settings::group('otp')->get(
                'otp_type'
            )) {
                $token = rand(
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit') - 1),
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit')) - 1
                );
            } else {
                $token = rand(pow(10, 4 - 1), pow(10, 4) - 1);
            }

            $otp = Otp::create([
                'phone' => $request->phone,
                'code' => $request->country_code,
                'token' => $token,
                'created_at' => now(),
            ]);

            if (!blank($otp)) {
                SendSmsCode::dispatch(
                    ['phone' => $request->post('phone'), 'code' => $request->post('country_code'), 'token' => $token]
                );
            }

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function resetOtpEmail(Request $request): bool
    {
        try {
            if (config('system.demo') == "True" || config('system.demo') == "TRUE" || config('system.demo') == "true" || config('system.demo') == 1) {
                return true;
            }
            $otp = DB::table('password_reset_tokens')->where([
                ['email', $request->post('email')]
            ]);

            if ($otp->exists()) {
                $otp->delete();
            }

            if (OtpType::EMAIL == Settings::group('otp')->get('otp_type') || OtpType::BOTH == Settings::group('otp')->get(
                'otp_type'
            )) {
                $token = rand(
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit') - 1),
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit')) - 1
                );
            } else {
                $token = rand(pow(10, 4 - 1), pow(10, 4) - 1);
            }
            // secure, always 6 characters long (pads with leading zeros if needed)
            $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $password_reset = DB::table('password_reset_tokens')->insert([
                'email'      => $request->post('email'),
                'token'      => $token,
                'created_at' => Carbon::now()
            ]);

            if (!blank($password_reset)) {
                SendEmailCodeNotificationJob::dispatch(['email' => $request->post('email'), 'pin' => $token]);
            }

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function otpEmail(Request $request): bool
    {
        try {
            if (config('system.demo') == "True" || config('system.demo') == "TRUE" || config('system.demo') == "true" || config('system.demo') == 1) {
                return true;
            }
            $otp = DB::table('password_reset_tokens')->where([
                ['email', $request->post('email')]
            ]);

            if ($otp->exists()) {
                $otp->delete();
            }

            if (OtpType::EMAIL == Settings::group('otp')->get('otp_type') || OtpType::BOTH == Settings::group('otp')->get(
                'otp_type'
            )) {
                $token = rand(
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit') - 1),
                    pow(10, (int)Settings::group('otp')->get('otp_digit_limit')) - 1
                );
            } else {
                $token = rand(pow(10, 4 - 1), pow(10, 4) - 1);
            }

            $password_reset = DB::table('password_reset_tokens')->insert([
                'email'      => $request->post('email'),
                'token'      => $token,
                'created_at' => Carbon::now()
            ]);

            if (!blank($password_reset)) {
                SendVerifyEmailCode::dispatch(['email' => $request->post('email'), 'pin' => $token]);
            }

            return true;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function verifyPhone(VerifyPhoneRequest $request): bool
    {
        try {
            if (config('system.demo') == "True" || config('system.demo') == "TRUE" || config('system.demo') == "true" || config('system.demo') == 1) {
                return true;
            }

            $otp = DB::table('otps')->where([
                ['phone', $request->post('phone')],
                ['token', $request->post('token')],
            ]);
            if ($otp->exists()) {
                $difference = Carbon::now()->diffInSeconds($otp->first()->created_at);
                if ($difference > (int)Settings::group('otp')->get('otp_expire_time') * 60) {
                    throw new Exception(trans('all.message.code_is_expired'), 422);
                } else {
                    $otp->delete();
                    return true;
                }
            } else {
                throw new Exception(trans('all.message.code_is_invalid'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request): bool
    {
        try {
            if (config('system.demo') == "True" || config('system.demo') == "TRUE" || config('system.demo') == "true" || config('system.demo') == 1) {
                return true;
            }

            $verify = DB::table('password_reset_tokens')->where([
                ['email', $request->post('email')],
                ['token', $request->post('token')],
            ]);
            if ($verify->exists()) {
                $verify->delete();
                return true;
            } else {
                throw new Exception(trans('all.message.code_is_invalid'), 422);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
