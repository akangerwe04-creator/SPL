<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDatabaseConnected
{
    /**
     * Memastikan aplikasi WAJIB terhubung ke database.
     * Tidak ada fallback temporary/offline.
     * Jika aplikasi tidak terhubung dengan database, aplikasi tidak dapat berjalan (HTTP 503).
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Uji koneksi PDO dan jalankan kueri verifikasi langsung ke database
            $pdo = DB::connection()->getPdo();
            if (!$pdo) {
                throw new \RuntimeException('Koneksi PDO ke database bernilai null.');
            }
            DB::select('SELECT 1');
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'database_error',
                    'message' => 'Aplikasi SPL 2026 tidak dapat berjalan karena tidak terhubung dengan database. Seluruh data wajib tersimpan secara permanen di database.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 503);
            }

            return response()->view('errors.database_disconnected', [
                'errorMessage' => $e->getMessage(),
            ], 503);
        }

        return $next($request);
    }
}

