<?php

namespace App\Http\Middleware;

use App\Models\Token;
use App\Models\User;
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
        $env = $is_sandbox ? 'sandbox' : 'production';

        if (str_contains($currentUrl, 'token')) {
            $clientId = $request->header('client-id');
            $clientSecret = $request->header('client-secret');
            $user = User::when($is_sandbox, function ($query) use ($clientId, $clientSecret) {
                $query->where('sbx_client_id', $clientId)->where('sbx_client_secret', $clientSecret);
            }, function ($query) use ($clientId, $clientSecret) {
                $query->where('client_id', $clientId)->where('client_secret', $clientSecret);
            })->first();
        } else {
            $refreshToken = $request->get('refresh_token');

            if (str_contains($refreshToken, '-')) {

                $seperation = explode('-', $refreshToken);
                $user_id = end($seperation);

                Token::where('created_at', '<=', Carbon::now()->subMinutes(5))
                    ->where('env', $env)
                    ->delete();

                $token = Token::where('user_id', $user_id)
                    ->where('token', $refreshToken)
                    ->where('env', $env)
                    ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                    ->first();

                if (is_null($token) || ! isset($token->user_id)) {

                    return response()->json([
                        'error' => 'Unauthorized',
                        'message' => 'Invalid client credentials.'
                    ], 401);
                }

                $user = User::find($token->user_id);

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

        config(['services.user' => $user->toArray()]);
        config('services.env', $user->env);

        return $next($request);
    }
}
