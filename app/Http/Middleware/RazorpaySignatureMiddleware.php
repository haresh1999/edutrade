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

        $env = str_contains($currentUrl, 'sandbox') ? 'sandbox' : 'production';

        $secret = config("services.razorpay.{$env}.key_sign");

        $payload = $request->except(['signature', 'callback_url', 'redirect_url']);

        ksort($payload);

        $payloadQueryString = http_build_query($payload);

        $receivedSignature = $request->signature ?? '';

        if (!$receivedSignature) {
            return response()->json([
                'status' => false,
                'error' => 'Signature missing or invalid signature provided'
            ], 401);
        }

        $calculatedSignature = hash_hmac('sha256', $payloadQueryString, $secret);

        if (!hash_equals($calculatedSignature, $receivedSignature)) {

            return response()->json([
                'status' => false,
                'error' => 'Invalid signature'
            ], 401);
        }

        return $next($request);
    }
}
