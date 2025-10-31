<?php

namespace App\Http\Middleware;

use App\Models\PhonepeUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PhonepeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUrl = url()->current();

        $clientId = $request->get('client_id');
        $clientSecret = $request->get('client_secret');
        $refreshToken = $request->get('refresh_token');
        $is_sandbox = str_contains($currentUrl, 'sandbox');

        $user = PhonepeUser::when($is_sandbox, function ($query) use ($clientId, $clientSecret, $refreshToken, $currentUrl) {
            if (str_contains($currentUrl, 'token')) {
                $query->where('sandbox_client_id', $clientId)->where('sandbox_client_secret', $clientSecret);
            } else {
                $query->where('refresh_token', $refreshToken);
            }
        }, function ($query) use ($clientId, $clientSecret, $refreshToken, $currentUrl) {
            if (str_contains($currentUrl, 'token')) {
                $query->where('client_id', $clientId)->where('client_secret', $clientSecret);
            } else {
                $query->where('refresh_token', $refreshToken);
            }
        })->first();

        if (is_null($user)) {

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid client credentials.'
            ], 401);
        }

        config(['services.phonepe.user' => $user->toArray()]);

        $user->update(['refresh_token' => null]);

        return $next($request);
    }
}
