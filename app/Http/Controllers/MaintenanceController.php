<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MaintenanceService;
use App\Models\CatalogSetting;
use App\Models\User;

class MaintenanceController extends Controller
{
    /**
     * Halaman Kontrol Mode Maintenance (Khusus Administrator)
     */
    public function index()
    {
        $info = MaintenanceService::getInfo();
        $isActive = MaintenanceService::isActive();
        $appSetting = CatalogSetting::current();

        $stats = [
            'total_users' => User::count(),
            'owner_count' => User::where('role', 'owner')->count(),
            'agent_count' => User::where('role', 'agent')->count(),
        ];

        return view('admin.maintenance', compact('info', 'isActive', 'appSetting', 'stats'));
    }

    /**
     * Aktifkan Mode Maintenance
     */
    public function start(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $message = $request->input('message') ?: 'mohon maaf server kami sedang dalam perbaikan, mohon maaf untuk sementara segala bentuk aktivitas tidak dapat dilakukan';

        MaintenanceService::start($user->id, $user->name, $message);

        return redirect()->route('admin.maintenance.index')
            ->with('warning', 'Mode Maintenance telah AKTIF. Seluruh pengguna selain Administrator (publik, agen, dan owner) kini diarahkan ke tampilan pemeliharaan server.');
    }

    /**
     * Hentikan Mode Maintenance (Kembali Normal)
     */
    public function stop()
    {
        MaintenanceService::stop();

        return redirect()->back()
            ->with('success', 'Mode Maintenance telah BERHASIL DIHENTIKAN. Seluruh pengguna dan publik kini dapat mengakses aplikasi kembali secara normal.');
    }

    /**
     * Pratinjau Tampilan Pemeliharaan yang Dilihat Publik
     */
    public function preview()
    {
        $maintenanceData = MaintenanceService::getInfo();
        $appSetting = CatalogSetting::current();

        return response()->view('maintenance', compact('maintenanceData', 'appSetting'));
    }
}
