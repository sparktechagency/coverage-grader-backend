<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalAuthenticate
{
    /**
     * If a bearer token or token input is present, attempt to authenticate the user via Sanctum.
     * Otherwise leave the request as guest.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // If already authenticated, nothing to do
        if (auth()->check()) {
            return $next($request);
        }

        // Try to find a token from Authorization header or token param
        $token = $request->bearerToken() ?? $request->query('token') ?? $request->input('token');

        if ($token) {
            try {
                $accessToken = PersonalAccessToken::findToken($token);
            } catch (\Throwable $e) {
                $accessToken = null;
            }

            if ($accessToken && $accessToken->tokenable) {
                // Set the current user for the request lifecycle
                auth()->setUser($accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
