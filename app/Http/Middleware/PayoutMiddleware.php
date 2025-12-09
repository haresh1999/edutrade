<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class PayoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $date = date('Ymd');

        $password = bcrypt("Professional@{$date}");

        if (Hash::check($request->header('password'), $password)) {

            return $next($request);
        }

        return response()->json([
            'error' => 'Unauthorized',
            'message' => 'Invalid client credentials.'
        ], 401);
    }
}
