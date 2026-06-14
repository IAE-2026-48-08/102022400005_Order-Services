<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Memproses request masuk dan melakukan otentikasi API Key via header X-IAE-KEY.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Mendapatkan API Key dari Request Header (case-insensitive)
        $apiKey = $request->header('X-IAE-KEY') ?: $request->header('x-iae-key');
        
        $expectedKey = config('services.central.api_key_local', '102022400005');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: API Key tidak ditemukan pada Request Header (gunakan header X-IAE-KEY).',
                'errors' => null
            ], 401);
        }

        if ($apiKey !== $expectedKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden: API Key tidak valid.',
                'errors' => null
            ], 403);
        }

        return $next($request);
    }
}
