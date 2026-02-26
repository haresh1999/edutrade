<?php

namespace App\Http\Middleware;

use App\Models\RazorpaySandboxToken;
use App\Models\RazorpayToken;
use App\Models\RazorpayUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        $currentUrl = url()->current();
        $is_sandbox = str_contains($currentUrl, 'sandbox');

        if (str_contains($currentUrl, 'token')) {
            $clientId = $request->header('client-id');
            $clientSecret = $request->header('client-secret');
            $user = RazorpayUser::when($is_sandbox, function ($query) use ($clientId, $clientSecret) {
                $query->where('sandbox_client_id', $clientId)->where('sandbox_client_secret', $clientSecret);
            }, function ($query) use ($clientId, $clientSecret) {
                $query->where('client_id', $clientId)->where('client_secret', $clientSecret);
            })->first();
        } else {
            $refreshToken = $request->get('refresh_token');
            if (str_contains($refreshToken, '-')) {
                $seperation = explode('-', $refreshToken);
                $user_id = end($seperation);
                if ($is_sandbox) {

                    RazorpaySandboxToken::where('created_at', '<=', Carbon::now()->subMinutes(5))->delete();

                    $token = RazorpaySandboxToken::where('user_id', $user_id)
                        ->where('token', $refreshToken)
                        ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                        ->first();
                } else {

                    RazorpayToken::where('created_at', '<=', Carbon::now()->subMinutes(5))->delete();

                    $token = RazorpayToken::where('user_id', $user_id)
                        ->where('token', $refreshToken)
                        ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                        ->first();
                }

                if (is_null($token) || ! isset($token->user_id)) {

                    return response()->json([
                        'error' => 'Unauthorized',
                        'message' => 'Invalid client credentials.'
                    ], 401);
                }

                $user = RazorpayUser::find($token->user_id);

                $token->delete();
            } else {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Invalid token format'
                ], 400);
            }
        }

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
