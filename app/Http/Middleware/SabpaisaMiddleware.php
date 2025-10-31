<?php

namespace App\Http\Middleware;

use App\Models\SabpaisaUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SabpaisaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $whitelistIps = [
            '206.237.35.13',
            '206.237.52.50',
            '206.237.35.36',
            '206.237.35.27',
            '206.237.52.50',
            '103.151.210.16',
            '206.237.35.129',
            '206.237.35.154',
            '47.76.96.185',
            '8.210.179.124',
            '8.210.248.4',
            '103.151.210.37',
            '206.237.32.129',
            '8.210.179.124',
        ];

        if (!in_array($request->ip(), $whitelistIps)) {

            return response()->json(['error' => 'Unauthorized request'], 403);
        }

        $clientId = $request->get('client_id');
        $clientSecret = $request->get('client_secret');
        $is_sandbox = str_contains(url()->current(), 'sandbox');

        $user = SabpaisaUser::when($is_sandbox, function ($query) use ($clientId, $clientSecret) {
            $query->where('sandbox_client_id', $clientId)->where('sandbox_client_secret', $clientSecret);
        }, function ($query) use ($clientId, $clientSecret) {
            $query->where('client_id', $clientId)->where('client_secret', $clientSecret);
        })->first();

        if (is_null($user)) {

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid client credentials.'
            ], 401);
        }

        config(['services.sabpaisa.user' => $user->toArray()]);

        return $next($request);
    }
}
