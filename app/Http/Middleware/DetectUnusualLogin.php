<?php

    namespace App\Http\Middleware;

    use App\Notifications\UnusualLoginAttempt;
    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Notification;
    use Smartisan\Settings\Facades\Settings;
    use Symfony\Component\HttpFoundation\Response;

    class DetectUnusualLogin
    {
        public function handle(Request $request, Closure $next) : Response
        {
            $response = $next( $request );

            // Only act right after a successful login
            if ( Auth::check() && $request->isMethod( 'POST' ) && $request->routeIs( 'login' ) && ! $response->isRedirect( route( 'login' ) ) ) {
                $this->checkAndNotify( $request );
            }

            return $response;
        }

        private function checkAndNotify(Request $request) : void
        {
            $user      = Auth::user();
            $ip        = $request->ip();
            $userAgent = $request->userAgent();
            $cacheKey  = "known_login_{$user->id}_{$ip}";

            // Only alert if this IP is new for this user (cached for 30 days)
            if ( Cache::has( $cacheKey ) ) {
                return;
            }

            Cache::put( $cacheKey , TRUE , now()->addDays( 30 ) );

            $settings   = Settings::group( 'notification' )->all();
            $adminEmail = $settings[ 'admin_email' ] ?? null;
            $adminPhone = $settings[ 'admin_phone' ] ?? null;

            $title   = 'Unusual Login Attempt Detected';
            $message = "A login was detected for user {$user->name} from a new device or IP address.";

            Notification::route( 'mail' , $adminEmail )
                        ->route( 'sms' , $adminPhone )
                        ->route( 'whatsapp' , $adminPhone )
                        ->notify( new UnusualLoginAttempt(
                            title   : $title ,
                            message : $message ,
                            ip      : $ip ,
                            device  : $userAgent ,
                        ) );
        }
    }