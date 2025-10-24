<?php

namespace App\Http\Middleware;

use App\Models\RazorpayUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RazorpayMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientId = $request->get('client_id');
        $clientSecret = $request->get('client_secret');
        $is_sandbox = str_contains(url()->current(), 'sandbox');

        $user = RazorpayUser::when($is_sandbox, function ($query) use ($clientId, $clientSecret) {
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

        config(['services.razorpay.user' => $user->toArray()]);

        return $next($request);
    }
}
