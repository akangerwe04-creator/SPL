<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CustomerPackage;
use App\Models\Payment;
use App\Models\AgentDeposit;
use App\Models\Package;
use App\Models\Item;
use App\Models\CatalogSetting;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ringkasan Finansial Utama
        $totalTarget = CustomerPackage::where('status', '!=', 'canceled')->sum('total_target');
        $totalVerifiedPaid = Payment::where('is_locked', true)->where('is_paid', true)->sum('amount');
        // Hanya hitung setoran yang sudah diajukan oleh agen (status: submitted)
        $totalPendingDeposit = AgentDeposit::where('status', 'submitted')->sum('total_amount');
        $remainingReceivable = max(0, $totalTarget - $totalVerifiedPaid);

        // 2. Statistik Member & Paket
        $totalActivePackages = CustomerPackage::where('status', 'active')->count();
        $totalCompletedPackages = CustomerPackage::where('status', 'completed')->count();
        $totalCanceledPackages = CustomerPackage::where('status', 'canceled')->count();

        // 3. Setoran Agen Menunggu Validasi Owner (Hanya berkas yang telah resmi diajukan agen)
        $pendingDeposits = AgentDeposit::with(['agent', 'payments.customerPackage.customer'])
            ->where('status', 'submitted')
            ->latest()
            ->take(6)
            ->get();

        // 4. Performa Agen Lapangan (Optimasi High Concurrency: GROUP BY single-query, mencegah 140+ N+1 queries)
        $verifiedSums = Payment::where('is_locked', true)
            ->where('is_paid', true)
            ->groupBy('agent_id')
            ->selectRaw('agent_id, SUM(amount) as total')
            ->pluck('total', 'agent_id');

        // Uang setoran yang sudah diajukan agen tetapi belum disahkan owner
        $inCartSums = Payment::where('is_locked', false)
            ->where('is_paid', true)
            ->whereHas('deposit', fn($q) => $q->where('status', 'submitted'))
            ->groupBy('agent_id')
            ->selectRaw('agent_id, SUM(amount) as total')
            ->pluck('total', 'agent_id');

        $agents = User::where('role', 'agent')
            ->withCount(['customers'])
            ->get()
            ->map(function ($agent) use ($verifiedSums, $inCartSums) {
                $agent->total_verified = (float)($verifiedSums[$agent->id] ?? 0);
                $agent->total_in_cart = (float)($inCartSums[$agent->id] ?? 0);
                return $agent;
            });

        // 5. Konsumen Menunggak yang berpotensi butuh Mini-POS (belum lunas)
        $laggingCustomers = CustomerPackage::with(['customer', 'package', 'agent'])
            ->where('status', 'active')
            ->where('total_paid', '<', \DB::raw('total_target'))
            ->orderBy('consecutive_unpaid_weeks', 'desc')
            ->take(5)
            ->get();

        // 6. Distribusi per Jenis Paket
        $packageStats = [
            'parsel' => CustomerPackage::whereHas('package', fn($q) => $q->where('type', 'parsel'))->count(),
            'tabungan' => CustomerPackage::whereHas('package', fn($q) => $q->where('type', 'tabungan'))->count(),
            'ecer' => CustomerPackage::whereHas('package', fn($q) => $q->where('type', 'ecer'))->count(),
        ];

        // 7. Statistik Tutup Buku & Titik Aman (Cached 60s agar dasbor super ringan saat diakses bersamaan)
        $closingStats = \Illuminate\Support\Facades\Cache::remember('spl_dashboard_closing_stats', 60, function () {
            $activeForClosing = CustomerPackage::with('package')->where('status', '!=', 'canceled')->get();
            $safe = $activeForClosing->filter(fn($cp) => $cp->isSafeThresholdReached())->count();
            return [
                'safe' => $safe,
                'unreached' => $activeForClosing->count() - $safe,
                'pending' => $activeForClosing->filter(fn($cp) => !$cp->isDecisionMade())->count(),
            ];
        });
        $safeReachedCount = $closingStats['safe'];
        $unreachedSafeCount = $closingStats['unreached'];
        $pendingDecisionCount = $closingStats['pending'];

        // 8. Pengaturan Aplikasi
        $appSetting = CatalogSetting::current();

        return view('owner.dashboard', compact(
            'totalTarget',
            'totalVerifiedPaid',
            'totalPendingDeposit',
            'remainingReceivable',
            'totalActivePackages',
            'totalCompletedPackages',
            'totalCanceledPackages',
            'pendingDeposits',
            'agents',
            'laggingCustomers',
            'packageStats',
            'safeReachedCount',
            'unreachedSafeCount',
            'pendingDecisionCount',
            'appSetting'
        ));
    }
}

