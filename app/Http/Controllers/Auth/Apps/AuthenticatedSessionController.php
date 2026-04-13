<?php

    namespace App\Http\Controllers\Auth\Apps;

    use Illuminate\Contracts\Auth\StatefulGuard;
    use Illuminate\Http\Request;
    use Illuminate\Routing\Controller;
    use Laravel\Fortify\Contracts\LogoutResponse;

    class AuthenticatedSessionController extends Controller
    {
        protected $guard;

        public function __construct(StatefulGuard $guard)
        {
            $this->guard = $guard;
        }

        public function destroy(Request $request) : LogoutResponse
        {
            $app_id = $request->header( 'X-App-Id' );
            activityLog( 'Logged out' , $app_id );
            $this->guard->logout();

            if ( $request->hasSession() ) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return app( LogoutResponse::class );
        }
    }
