<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MaintenanceService;
use App\Models\CatalogSetting;

class CheckCustomMaintenanceMode
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika mode maintenance tidak aktif, lanjutkan request
        if (!MaintenanceService::isActive()) {
            return $next($request);
        }

        // 2. Jika user sudah login dan merupakan ADMIN (Administrator):
        // Administrator memiliki hak penuh untuk mengakses aplikasi seutuhnya!
        $user = $request->user();
        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        // 3. Rute yang dikecualikan agar administrator tetap dapat login dan mengelola sistem:
        if (
            $request->is('login') ||
            $request->is('logout') ||
            $request->is('maintenance/preview') ||
            $request->routeIs('login') ||
            $request->routeIs('logout') ||
            $request->routeIs('maintenance.preview')
        ) {
            return $next($request);
        }

        // 4. Jika user sedang login tapi BUKAN admin (misal Owner atau Staf Agen):
        // Logout otomatis agar tidak ada aktivitas di dalam sistem selama maintenance
        if ($user && !$user->isAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        // 5. Untuk request JSON / API:
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'maintenance' => true,
                'message' => 'Mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan.',
            ], 503);
        }

        // 6. Tampilkan halaman pemeliharaan server untuk publik, tamu, agen, dan owner
        $maintenanceData = MaintenanceService::getInfo();
        try {
            $appSetting = CatalogSetting::current();
        } catch (\Throwable $e) {
            $appSetting = new CatalogSetting([
                'app_name' => 'SUSI PARSEL',
                'title' => 'SISTEM MANAJEMEN PARSEL & TABUNGAN HARI RAYA',
            ]);
        }

        return response()->view('maintenance', compact('maintenanceData', 'appSetting'), 503);
    }
}
