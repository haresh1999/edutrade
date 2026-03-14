<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUrl = url()->current();
        $env = str_contains($currentUrl, 'sandbox') ? 'sandbox' : 'production';

        $clientId = $request->header('client-id');

        $clientSecret = $request->header('client-secret');

        $user = User::when($env == 'sandbox', function ($query) use ($clientId, $clientSecret) {
            $query->where('sbx_client_id', $clientId)->where('sbx_client_secret', $clientSecret);
        }, function ($query) use ($clientId, $clientSecret) {
            $query->where('client_id', $clientId)->where('client_secret', $clientSecret);
        })->first();

        if (is_null($user)) {

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid client credentials.'
            ], 401);
        }

        config(['services.user' => $user->toArray()]);
        config('services.env', $env);

        return $next($request);
    }
}
