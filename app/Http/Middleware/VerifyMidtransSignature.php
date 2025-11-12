<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMidtransSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $serverKey = config('services.midtrans.server_key');
        $orderId = $request->input('order_id');
        $statusCode = $request->input('status_code');
        $grossAmount = $request->input('gross_amount');
        $signatureKey = $request->input('signature_key');

        // Create signature hash
        $hash = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        // Verify signature
        if ($hash !== $signatureKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        return $next($request);
    }
}
