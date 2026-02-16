<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function boot(Request $request): void
    {
        require_once app_path('Helpers/functions.php');
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        $origin = $request->header('Origin');
        $host = parse_url($origin, PHP_URL_HOST);

        // If the request comes from your root domain, trust it as stateful
        if ($host && (str_ends_with($host, '.smartduuka2.test') || str_ends_with($host, '.smartduuka.com'))) {
            $existing = config('sanctum.stateful', []);
            config(['sanctum.stateful' => array_merge($existing, [$host])]);
        }
    }
}
