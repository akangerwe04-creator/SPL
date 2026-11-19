<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WholesalePlannerService;
use App\Services\WholesaleProcurementExportService;

class InventoryPlannerController extends Controller
{
    protected WholesalePlannerService $plannerService;
    protected WholesaleProcurementExportService $exportService;

    public function __construct(WholesalePlannerService $plannerService, WholesaleProcurementExportService $exportService)
    {
        $this->plannerService = $plannerService;
        $this->exportService = $exportService;
    }

    public function index(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $result = $this->plannerService->calculateRequirements($scope);
        $packagesToPrepare = $result['packages_to_prepare'] ?? [];
        $items = $result['items'];
        $bonusItems = $result['bonus_items'];
        $packagingItems = $result['packaging_items'];
        $summary = $result['summary'];
        $cashItems = $result['cash_items'];
        $cashByAgent = $result['cash_by_agent'];
        $excludedIncompleteCustomers = $result['excluded_incomplete_customers'] ?? [];

        // Filter tipe paket jika diminta
        if ($request->filled('package_type')) {
            $ptype = $request->package_type;
            $packagesToPrepare = array_filter($packagesToPrepare, fn($p) => $p['package_type'] === $ptype);
        }

        // Filter hanya paket yang memiliki pesanan aktif jika diminta
        if ($request->filled('only_with_orders') && $request->only_with_orders === '1') {
            $packagesToPrepare = array_filter($packagesToPrepare, fn($p) => $p['total_orders'] > 0);
        }

        // Filter kategori jika diminta (pada barang pokok)
        if ($request->filled('category')) {
            $cat = $request->category;
            $items = array_filter($items, fn($i) => $i['category'] === $cat);
        }

        // Pencarian nama barang, bonus, kemasan, atau nama konsumen / agen / paket
        if ($request->filled('search')) {
            $s = strtolower($request->search);
            $packagesToPrepare = array_filter($packagesToPrepare, function ($p) use ($s) {
                return str_contains(strtolower($p['package_name']), $s) ||
                       str_contains(strtolower($p['package_code']), $s) ||
                       str_contains(strtolower($p['package_type_label']), $s) ||
                       collect($p['customers'])->contains(fn($c) => str_contains(strtolower($c['customer_name']), $s) || str_contains(strtolower($c['agent_name']), $s));
            });
            $items = array_filter($items, function ($i) use ($s) {
                return str_contains(strtolower($i['item_name']), $s) || str_contains(strtolower($i['item_code']), $s);
            });
            $bonusItems = array_filter($bonusItems, function ($b) use ($s) {
                return str_contains(strtolower($b['item_name']), $s) || str_contains(strtolower($b['item_code']), $s);
            });
            $packagingItems = array_filter($packagingItems, function ($p) use ($s) {
                return str_contains(strtolower($p['name']), $s) || str_contains(strtolower($p['code']), $s);
            });
            $cashItems = array_filter($cashItems, function ($c) use ($s) {
                return str_contains(strtolower($c['customer_name']), $s) || 
                       str_contains(strtolower($c['agent_name']), $s) ||
                       str_contains(strtolower($c['package_name']), $s) ||
                       str_contains(strtolower($c['cash_description']), $s);
            });
            $excludedIncompleteCustomers = array_filter($excludedIncompleteCustomers, function ($c) use ($s) {
                return str_contains(strtolower($c['customer_name']), $s) ||
                       str_contains(strtolower($c['customer_code']), $s) ||
                       str_contains(strtolower($c['agent_name']), $s) ||
                       str_contains(strtolower($c['package_name']), $s);
            });
        }

        return view('inventory.planner', compact(
            'scope',
            'packagesToPrepare',
            'items',
            'bonusItems',
            'packagingItems',
            'summary',
            'cashItems',
            'cashByAgent',
            'excludedIncompleteCustomers'
        ));
    }

    /**
     * Tampilan Siap Cetak Lembar Rencana Belanja Grosir, Bonus, Kemasan & Penyiapan Uang Tunai
     */
    public function printView(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $result = $this->plannerService->calculateRequirements($scope);
        $packagesToPrepare = $result['packages_to_prepare'] ?? [];
        $items = $result['items'];
        $bonusItems = $result['bonus_items'];
        $packagingItems = $result['packaging_items'];
        $summary = $result['summary'];
        $cashItems = $result['cash_items'];
        $cashByAgent = $result['cash_by_agent'];
        $excludedIncompleteCustomers = $result['excluded_incomplete_customers'] ?? [];

        return view('inventory.print', compact(
            'scope',
            'packagesToPrepare',
            'items',
            'bonusItems',
            'packagingItems',
            'summary',
            'cashItems',
            'cashByAgent',
            'excludedIncompleteCustomers'
        ));
    }

    /**
     * Ekspor Excel (.xlsx) Rekapitulasi Pengadaan Barang & Kemasan ke Supplier
     */
    public function exportXlsx(Request $request)
    {
        $scope = $request->input('scope', 'all');
        $result = $this->plannerService->calculateRequirements($scope);

        return $this->exportService->exportXlsx($result, $scope);
    }
}
