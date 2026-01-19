<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class ForceAdminLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isLocal() && !auth('sanctum')->check()) {
            $user = User::first();

            if ($user) {
                // This is the magic line for Sanctum
                Sanctum::actingAs($user, ['*']);
            }
        }

        return $next($request);
    }
}
