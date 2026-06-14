<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class JwtSsoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Bearer ')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token JWT tidak ditemukan pada header Authorization.',
                'errors' => null
            ], 401);
        }

        $jwt = substr($authorization, 7);

        try {
            // 1. Ambil Public Keys (JWKS) dari SSO Cloud Dosen
            $jwksUrl = 'https://iae-sso.virtualfri.id/api/v1/auth/jwks';
            $response = Http::get($jwksUrl);

            if ($response->failed()) {
                throw new Exception('Gagal menghubungi SSO Server untuk verifikasi.');
            }

            // 2. Decode dan verifikasi JWT
            $keys = JWK::parseKeySet($response->json());
            $decoded = JWT::decode($jwt, $keys);

            // 3. Ekstraksi Payload JWT
            // Membaca email dari profile.email, atau sub jika profile kosong
            $email = $decoded->profile->email ?? $decoded->sub ?? null;

            // Membaca nama dari profile.name, atau default dari email
            $userName = $decoded->profile->name ?? $decoded->name ?? explode('@', $email)[0];

            // Membaca role dari token_type (contoh: 'user' dipetakan ke 'warga')
            $roleName = $decoded->role ?? ($decoded->token_type === 'user' ? 'warga' : 'guest');        

            if (!$email) {
                $payloadStr = json_encode($decoded);
                throw new Exception("Payload JWT tidak menyertakan email pengguna. Payload: {$payloadStr}");
            }

            // 4. Proses Pemetaan ke Tabel Roles & Users Lokal (Sesuai Rubrik)
            DB::transaction(function () use ($email, $roleName, $userName) {
                // Cari atau buat role lokal baru
                $roleId = DB::table('roles')->updateOrInsert(
                    ['name' => $roleName],
                    ['description' => 'Role dipetakan dari SSO Terpusat']
                );
                    
                // Ambil id role yang baru saja diproses
                $dbRole = DB::table('roles')->where('name', $roleName)->first();

                // Daftarkan atau perbarui user lokal berdasarkan email SSO
                DB::table('users')->updateOrInsert(
                    ['email' => $email],
                    [
                        'name' => $userName,
                        'role_id' => $dbRole->id,
                        'updated_at' => now()
                    ]
                );
            });

            // 5. Teruskan payload user yang terotentikasi ke dalam request Laravel
            $request->attributes->set('auth_user', $decoded);

            return $next($request);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Token JWT tidak valid atau kedaluwarsa. ' . $e->getMessage(),
                'errors' => null
            ], 401);
        }
    }
}