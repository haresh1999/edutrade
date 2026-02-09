<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RazorpaySignatureMiddleware
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

        $secret = config("services.razorpay.{$is_sandbox}.key_sign");

        // 1. Get raw body (VERY IMPORTANT)
        $payload = $request->getContent();

        // 2. Get signature from header
        $receivedSignature = $request->header('X-Provider-Signature');

        if (!$receivedSignature) {

            return response()->json(['error' => 'Signature missing'], 401);
        }

        // 3. Generate signature
        $calculatedSignature = hash_hmac('sha256', $payload, $secret);

        // 4. Timing-safe comparison
        if (!hash_equals($calculatedSignature, $receivedSignature)) {

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }
}
