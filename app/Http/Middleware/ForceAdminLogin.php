<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForceAdminLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isLocal() && !Auth::check()) {
            $user = User::first();

            if ($user) {
                Auth::login($user);
            }
        }

        return $next($request);
    }
}
