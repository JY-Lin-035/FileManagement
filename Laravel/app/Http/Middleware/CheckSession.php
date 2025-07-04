<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

use Closure;
use Exception;

use Illuminate\Support\Facades\Log;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $start = now();
        try {
            $token = $request->cookie('session');
            [$id, $tokenValue] = explode('|', $token, 2);
            $dbToken = PersonalAccessToken::find($id);

            if (!$dbToken || !hash_equals($dbToken->token, hash('sha256', $tokenValue)) || $dbToken->expires_at->isPast()) {
                return response()->json(null, 401);
            } else {
                $request->attributes->set('userId', $dbToken->tokenable_id);

                $response = $next($request);
                $dbToken->expires_at = now()->addMinutes((int) env('TOKEN_EXPIRE_TIME', 30));
                $dbToken->save();
                $response->headers->setCookie(cookie('session', $token, (int) env('TOKEN_EXPIRE_TIME', 30)));
                return $response;
            }
        } catch (Exception $e) {
            return response()->json(null, 401);
        }
    }
}
