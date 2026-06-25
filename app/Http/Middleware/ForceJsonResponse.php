<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Paksa semua request ke API menggunakan format JSON.
     * Mencegah Laravel mengembalikan HTML pada kondisi error apapun.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Paksa header Accept menjadi application/json
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
