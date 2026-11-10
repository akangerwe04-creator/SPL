<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Payment;
use App\Models\Package;
use App\Models\Item;
use App\Models\CatalogSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AgentManagementController extends Controller
{
    /**
     * Tampilkan Menu Agen (Daftar Tim Agen & Konsumen Binaan)
     */
    public function index(Request $request)
    {
        $agents = User::where('role', 'agent')
            ->withCount('customers')
            ->orderBy('name')
            ->get();

        // Hitung total setoran & tagihan per agen (Optimasi High Concurrency: 2 query GROUP BY menggantikan 140+ N+1 queries)
        $collectedSums = Payment::where('is_paid', true)
            ->groupBy('agent_id')
            ->selectRaw('agent_id, SUM(amount) as total')
            ->pluck('total', 'agent_id');

        $targetSums = CustomerPackage::where('status', '!=', 'canceled')
            ->groupBy('agent_id')
            ->selectRaw('agent_id, SUM(total_target) as total')
            ->pluck('total', 'agent_id');

        foreach ($agents as $agent) {
            $agent->total_collected = (float)($collectedSums[$agent->id] ?? 0);
            $agent->total_target = (float)($targetSums[$agent->id] ?? 0);
        }

        // Konsumen binaan
        $customerQuery = Customer::with(['agent', 'customerPackages.package']);
        if ($request->filled('agent_id')) {
            $customerQuery->where('agent_id', $request->agent_id);
        }
        $customers = $customerQuery->orderBy('name')->paginate(15);

        $totalAgents = $agents->count();
        $totalCustomers = Customer::count();
        $totalCollected = Payment::where('is_paid', true)->sum('amount');

        return view('owner.agents.index', compact('agents', 'customers', 'totalAgents', 'totalCustomers', 'totalCollected'));
    }

    /**
     * Tambah akun agen lapangan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username|alpha_dash',
            'phone' => 'required|string|max:25',
            'address' => 'nullable|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:4',
        ]);

        if (empty($validated['email'])) {
            $validated['email'] = strtolower($validated['username']) . '@spl.local';
        }

        $validated['plain_password'] = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'agent';
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('owner.agents.index')->with('success', 'Akun agen lapangan baru berhasil didaftarkan.');
    }

    /**
     * Perbarui data akun agen
     */
    public function update(Request $request, User $agent)
    {
        if (!$agent->isAgent()) {
            return back()->with('error', 'Hanya akun agen yang dapat dikelola dari menu ini.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,' . $agent->id,
            'phone' => 'required|string|max:25',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:users,email,' . $agent->id,
            'password' => 'nullable|string|min:4',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (!empty($validated['password'])) {
            $validated['plain_password'] = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Jika username berubah dan email sebelumnya adalah email bawaan @spl.local, update juga emailnya
        if (empty($validated['email'])) {
            if (empty($agent->email) || str_ends_with($agent->email, '@spl.local')) {
                $validated['email'] = strtolower($validated['username']) . '@spl.local';
            } else {
                unset($validated['email']);
            }
        }

        $agent->update($validated);

        return redirect()->route('owner.agents.index')->with('success', "Data akun agen {$agent->name} berhasil diperbarui.");
    }

    /**
     * Nonaktifkan, aktifkan, atau hapus permanen akun agen
     */
    public function destroy(Request $request, User $agent)
    {
        if (!$agent->isAgent()) {
            return back()->with('error', 'Hanya akun agen yang dapat dikelola dari menu ini.');
        }

        $agentName = $agent->name;
        $hasData = $agent->customers()->exists() || $agent->deposits()->exists();

        // 1. Jika request meminta nonaktifkan akun agen
        if ($request->input('action') === 'deactivate') {
            $agent->update(['is_active' => false]);
            return redirect()->route('owner.agents.index')
                ->with('success', "Akun agen {$agentName} berhasil dinonaktifkan (data binaan tetap tersimpan aman).");
        }

        // 2. Jika request meminta aktifkan kembali akun agen
        if ($request->input('action') === 'activate') {
            $agent->update(['is_active' => true]);
            return redirect()->route('owner.agents.index')
                ->with('success', "Akun agen {$agentName} berhasil diaktifkan kembali.");
        }

        // 3. Jika agen memiliki data binaan dan bukan force delete
        if ($hasData && !$request->boolean('force')) {
            $agent->update(['is_active' => false]);
            return redirect()->route('owner.agents.index')
                ->with('success', "Akun agen {$agentName} memiliki data konsumen/setoran aktif sehingga statusnya dinonaktifkan demi keamanan data. Gunakan opsi Hapus Permanen jika ingin membersihkan seluruh datanya.");
        }

        // 4. Hapus permanen akun agen
        DB::transaction(function () use ($agent) {
            // Bersihkan interaksi sosial
            $agent->postLikes()->delete();
            $agent->comments()->delete();
            $agent->posts()->delete();

            // Jika ada data binaan konsumen, bersihkan paket dan konsumen terkait
            foreach ($agent->customers as $customer) {
                foreach ($customer->customerPackages as $cp) {
                    $cp->payments()->delete();
                    $cp->customItems()->delete();
                    $cp->partialFulfillment?->items()->delete();
                    $cp->partialFulfillment?->delete();
                    $cp->delete();
                }
                $customer->delete();
            }

            // Hapus setoran agen
            $agent->deposits()->delete();

            // Hapus akun agen
            $agent->delete();
        });

        return redirect()->route('owner.agents.index')
            ->with('success', "Akun agen {$agentName} berhasil dihapus permanen dari sistem.");
    }

    /**
     * Laporan Keseluruhan Distribusi Agen, Konsumen, dan Persebaran Paket
     */
    public function report(Request $request)
    {
        $data = $this->buildReportData($request);
        return view('owner.agents.report', $data);
    }

    /**
     * Versi Cetak / Print Brosur Laporan Distribusi Agen & Paket
     */
    public function reportPrint(Request $request)
    {
        $data = $this->buildReportData($request);
        return view('owner.agents.report_print', $data);
    }

    /**
     * Ekspor Laporan Distribusi Agen ke format Excel (.xlsx) Multi-sheet Siap Cetak
     */
    public function reportExportXlsx(Request $request, \App\Services\AgentReportExportService $exportService)
    {
        $data = $this->buildReportData($request);
        return $exportService->exportXlsx($data);
    }

    /**
     * Ekspor Master Data Akun, No WA, Alamat, Username & Password Agen Lapangan (.xlsx)
     */
    public function exportAccountsXlsx(Request $request, \App\Services\AgentReportExportService $exportService)
    {
        $agents = User::where('role', 'agent')
            ->withCount('customers')
            ->orderBy('name')
            ->get();

        $sums = Payment::where('is_paid', true)
            ->groupBy('agent_id')
            ->selectRaw('agent_id, SUM(amount) as total')
            ->pluck('total', 'agent_id');

        foreach ($agents as $agent) {
            $agent->total_collected = (float)($sums[$agent->id] ?? 0);
        }

        $appSetting = CatalogSetting::current();

        return $exportService->exportAgentAccountsXlsx($agents, $appSetting);
    }

    /**
     * Download format / template import data agen (.xlsx atau .csv)
     */
    public function downloadImportTemplate(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
        $format = strtolower($request->query('format', 'xlsx'));

        $headers = [
            'Nama Lengkap Agen*',
            'Username Login*',
            'Kata Sandi / Password',
            'Nomor WhatsApp / HP*',
            'Alamat Domisili / Operasional',
            'Email',
            'Status (Aktif/Nonaktif)',
        ];

        $sampleRows = [
            [
                'Ahmad Hidayat',
                'ahmad',
                'password',
                '0812-3456-7890',
                'Kp. Babakan RT 02/RW 04, Majalaya',
                'ahmad@spl.local',
                'Aktif',
            ],
            [
                'Siti Nurhaliza',
                'sitinur',
                'password123',
                '0813-9876-5432',
                'Jl. Raya Paseh No. 45',
                'sitinur@spl.local',
                'Aktif',
            ],
            [
                'Rian Prasetyo',
                'rianprasetyo',
                'password',
                '0857-1122-3344',
                'Desa Cikancung RT 01/RW 05',
                '',
                'Aktif',
            ],
        ];

        $colWidths = [
            1 => 28,
            2 => 18,
            3 => 22,
            4 => 22,
            5 => 38,
            6 => 26,
            7 => 16,
        ];

        if ($format === 'csv') {
            $csv = $excelHelper->createCsv($headers, $sampleRows);
            return response($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="Template_Import_Agen_SPL.csv"',
            ]);
        }

        $xlsx = $excelHelper->createXlsx(
            'TEMPLATE IMPORT AGEN',
            $headers,
            $sampleRows,
            'TEMPLATE IMPORT DATA AGEN LAPANGAN SPL',
            'Petunjuk: Kolom bertanda (*) wajib diisi. Kata sandi default adalah "password" jika dikosongkan.',
            $colWidths
        );

        return response($xlsx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="Template_Import_Agen_SPL.xlsx"',
        ]);
    }

    /**
     * Proses import data agen dari file Excel (.xlsx) atau CSV
     */
    public function import(Request $request, \App\Services\SimpleExcelHelper $excelHelper)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'duplicate_action' => 'nullable|string|in:update,skip',
        ]);

        $file = $request->file('file');
        $duplicateAction = $request->input('duplicate_action', 'update');

        try {
            $rows = $excelHelper->parseFile($file->getPathname(), $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File yang diunggah kosong atau tidak memiliki data.');
        }

        // Cari baris header (baris yang mengandung 'nama' atau 'username')
        $headerRowIdx = -1;
        foreach ($rows as $idx => $row) {
            $lineStr = strtolower(implode(' ', $row));
            if (str_contains($lineStr, 'nama') && (str_contains($lineStr, 'username') || str_contains($lineStr, 'login') || str_contains($lineStr, 'whatsapp') || str_contains($lineStr, 'hp'))) {
                $headerRowIdx = $idx;
                break;
            }
        }

        if ($headerRowIdx === -1) {
            $headerRowIdx = 0;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        $dataRows = array_slice($rows, $headerRowIdx + 1);

        foreach ($dataRows as $lineNo => $row) {
            $realLineNumber = $headerRowIdx + $lineNo + 2;

            $name = trim($row[0] ?? '');
            $rawUsername = trim($row[1] ?? '');
            $password = trim($row[2] ?? '');
            $phone = trim($row[3] ?? '');
            $address = trim($row[4] ?? '');
            $email = trim($row[5] ?? '');
            $statusStr = strtolower(trim($row[6] ?? 'aktif'));

            if ($name === '' && $rawUsername === '' && $phone === '') {
                continue;
            }

            if ($name === '') {
                $errors[] = "Baris {$realLineNumber}: Nama agen tidak boleh kosong.";
                $skipped++;
                continue;
            }

            $username = strtolower(preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $rawUsername));
            if ($username === '') {
                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($name, 0, 15)));
                if ($username === '') {
                    $username = 'agen' . rand(100, 999);
                }
            }

            if ($password === '') {
                $password = 'password';
            }

            if ($phone === '') {
                $phone = '-';
            }

            if ($email === '') {
                $email = "{$username}@spl.local";
            }

            $isActive = !in_array($statusStr, ['nonaktif', 'tidak aktif', '0', 'false', 'inactive']);

            $existing = User::where('username', $username)->first();

            if ($existing) {
                if ($duplicateAction === 'skip') {
                    $skipped++;
                    continue;
                }

                $existing->update([
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address ?: $existing->address,
                    'is_active' => $isActive,
                    'plain_password' => $password,
                    'password' => Hash::make($password),
                ]);

                if (str_ends_with($existing->email, '@spl.local') && !empty($email)) {
                    if (!User::where('email', $email)->where('id', '!=', $existing->id)->exists()) {
                        $existing->update(['email' => $email]);
                    }
                }

                $updated++;
            } else {
                $finalEmail = $email;
                if (User::where('email', $finalEmail)->exists()) {
                    $finalEmail = "{$username}." . rand(10, 99) . "@spl.local";
                }

                User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $finalEmail,
                    'phone' => $phone,
                    'address' => $address ?: null,
                    'role' => 'agent',
                    'is_active' => $isActive,
                    'plain_password' => $password,
                    'password' => Hash::make($password),
                ]);

                $created++;
            }
        }

        $msg = "Import data agen selesai! Berhasil menambahkan {$created} agen baru";
        if ($updated > 0) {
            $msg .= ", memperbarui {$updated} agen";
        }
        if ($skipped > 0) {
            $msg .= ", melewati {$skipped} agen";
        }
        $msg .= ".";

        if (!empty($errors)) {
            return redirect()->route('owner.agents.index')
                ->with('success', $msg)
                ->with('warning', 'Beberapa baris data dilewati: ' . implode(' | ', array_slice($errors, 0, 3)));
        }

        return redirect()->route('owner.agents.index')->with('success', $msg);
    }

    /**
     * Helper penyusun data laporan rekapitulasi hierarkis
     */
    private function buildReportData(Request $request): array
    {
        // 1. Ambil paket-paket aktif terkelompok
        $packages = Package::where('is_active', true)
            ->with(['packageItems.item'])
            ->orderBy('period_type')
            ->orderBy('id')
            ->get();

        // A. Paket Parsel Harian (period_type == 'harian' & bukan eceran)
        $harianPackages = $packages->filter(fn($p) => $p->period_type === 'harian' && $p->type !== 'ecer')->values();

        // B. Paket Mingguan Lainnya / Standar (period_type == 'mingguan' & bukan eceran)
        $mingguanPackages = $packages->filter(fn($p) => ($p->period_type === 'mingguan' || empty($p->period_type)) && $p->type !== 'ecer')->values();

        // C. Paket Tabungan Lebaran (type == 'tabungan', jika ada)
        $tabunganPackages = $packages->filter(fn($p) => $p->type === 'tabungan')->values();

        // D. Paket Mingguan Eceran (type == 'ecer')
        $ecerPackages = $packages->filter(fn($p) => $p->type === 'ecer')->values();

        // 2. Query data agen beserta konsumen dan paket yang dipilih
        $agentsQuery = User::where('role', 'agent')
            ->with([
                'customers' => function ($q) {
                    $q->with([
                        'customerPackages.package.packageItems.item',
                        'customerPackages.customItems.item',
                    ])->orderBy('name');
                }
            ])
            ->orderBy('name');

        if ($request->filled('agent_id')) {
            $agentsQuery->where('id', $request->agent_id);
        }

        $agents = $agentsQuery->get();

        // 3. Kumpulkan seluruh nama-nama barang eceran unik se-SPL
        $ecerItemNames = [];

        // Master Item kategori 'Barang Eceran'
        $masterEcerItems = Item::where('is_active', true)
            ->where('category', 'Barang Eceran')
            ->pluck('name')
            ->toArray();
        foreach ($masterEcerItems as $mName) {
            $mName = trim($mName);
            if (!empty($mName)) {
                $ecerItemNames[$mName] = true;
            }
        }

        // Dari Paket Eceran yang terdaftar
        foreach ($ecerPackages as $ep) {
            if ($ep->packageItems->isNotEmpty()) {
                foreach ($ep->packageItems as $pi) {
                    $name = $pi->item ? trim($pi->item->name) : '';
                    if (!empty($name)) {
                        $ecerItemNames[$name] = true;
                    }
                }
            } else {
                $cleanName = preg_replace('/^Paket\s+(Parsel\s+)?Eceran\s+/i', '', trim($ep->name));
                $ecerItemNames[$cleanName ?: $ep->name] = true;
            }
        }

        // Dari pesanan custom konsumen paket eceran
        foreach ($agents as $agent) {
            foreach ($agent->customers as $customer) {
                foreach ($customer->customerPackages as $cp) {
                    if ($cp->package && $cp->package->isEcer()) {
                        if ($cp->customItems->isNotEmpty()) {
                            foreach ($cp->customItems as $ci) {
                                $name = $ci->item ? trim($ci->item->name) : '';
                                if (!empty($name)) {
                                    $ecerItemNames[$name] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Jika master barang eceran masih belum ada yang terdaftar, siapkan beberapa item standar
        if (empty($ecerItemNames)) {
            $sampleEcerItems = ['Daging Sapi', 'Daging Ayam', 'Minyak Goreng', 'Beras Ramos', 'Gula Pasir'];
            foreach ($sampleEcerItems as $sm) {
                $ecerItemNames[$sm] = true;
            }
        }

        ksort($ecerItemNames);
        $ecerItemColumns = array_keys($ecerItemNames);

        // 4. Inisialisasi total akumulator se-SPL
        $packageTotals = [];
        foreach ($packages as $pkg) {
            $packageTotals[$pkg->id] = 0;
        }

        $harianPackageTotals = [];
        foreach ($harianPackages as $pkg) {
            $harianPackageTotals[$pkg->id] = 0;
        }

        $mingguanPackageTotals = [];
        foreach ($mingguanPackages as $pkg) {
            $mingguanPackageTotals[$pkg->id] = 0;
        }

        $tabunganPackageTotals = [];
        foreach ($tabunganPackages as $pkg) {
            $tabunganPackageTotals[$pkg->id] = 0;
        }

        $ecerItemTotals = [];
        foreach ($ecerItemColumns as $colName) {
            $ecerItemTotals[$colName] = [
                'customers' => 0,
                'qty' => 0,
            ];
        }

        $totalCustomersCount = 0;
        $totalPackagesCount = 0;
        $totalTargetNominalSum = 0;
        $totalPaidSum = 0;

        $totalHarianCustomersCount = 0;
        $totalHarianOrdersCount = 0;
        $totalMingguanCustomersCount = 0;
        $totalMingguanOrdersCount = 0;
        $totalEcerCustomersCount = 0;
        $totalEcerOrdersCount = 0;
        $totalTabunganCustomersCount = 0;
        $totalTabunganOrdersCount = 0;

        // 5. Rekapitulasi per agen & per paket
        foreach ($agents as $agent) {
            $agentPackageCounts = [];
            foreach ($packages as $pkg) {
                $agentPackageCounts[$pkg->id] = 0;
            }

            $agentHarianPackageCounts = [];
            foreach ($harianPackages as $pkg) {
                $agentHarianPackageCounts[$pkg->id] = 0;
            }

            $agentMingguanPackageCounts = [];
            foreach ($mingguanPackages as $pkg) {
                $agentMingguanPackageCounts[$pkg->id] = 0;
            }

            $agentTabunganPackageCounts = [];
            foreach ($tabunganPackages as $pkg) {
                $agentTabunganPackageCounts[$pkg->id] = 0;
            }

            $agentEcerItemCounts = []; // [itemName => jumlah konsumen agen yang mengambil barang ini]
            $agentEcerItemQty = [];    // [itemName => total quantity barang ini]
            foreach ($ecerItemColumns as $colName) {
                $agentEcerItemCounts[$colName] = 0;
                $agentEcerItemQty[$colName] = 0;
            }

            $harianCustSet = [];
            $mingguanCustSet = [];
            $ecerCustSet = [];
            $tabunganCustSet = [];

            $agentOrdersCount = 0;
            $agentTargetSum = 0;
            $agentPaidSum = 0;

            $agentHarianOrdersCount = 0;
            $agentMingguanOrdersCount = 0;
            $agentEcerOrdersCount = 0;
            $agentTabunganOrdersCount = 0;

            foreach ($agent->customers as $customer) {
                foreach ($customer->customerPackages as $cp) {
                    $pkg = $cp->package;
                    if (!$pkg) continue;

                    $agentPackageCounts[$pkg->id] = ($agentPackageCounts[$pkg->id] ?? 0) + 1;
                    $packageTotals[$pkg->id] = ($packageTotals[$pkg->id] ?? 0) + 1;
                    $agentOrdersCount++;
                    $totalPackagesCount++;

                    $agentTargetSum += (float) $cp->total_target;
                    $agentPaidSum += (float) $cp->total_paid;

                    // A. Parsel Harian (Harian & Non-Eceran)
                    if ($pkg->isHarian() && !$pkg->isEcer()) {
                        $harianCustSet[$customer->id] = true;
                        $agentHarianPackageCounts[$pkg->id] = ($agentHarianPackageCounts[$pkg->id] ?? 0) + 1;
                        $harianPackageTotals[$pkg->id] = ($harianPackageTotals[$pkg->id] ?? 0) + 1;
                        $agentHarianOrdersCount++;
                        $totalHarianOrdersCount++;
                    }

                    // B. Paket Mingguan Lainnya / Standar (Mingguan & Non-Eceran)
                    if ($pkg->isMingguan() && !$pkg->isEcer()) {
                        $mingguanCustSet[$customer->id] = true;
                        $agentMingguanPackageCounts[$pkg->id] = ($agentMingguanPackageCounts[$pkg->id] ?? 0) + 1;
                        $mingguanPackageTotals[$pkg->id] = ($mingguanPackageTotals[$pkg->id] ?? 0) + 1;
                        $agentMingguanOrdersCount++;
                        $totalMingguanOrdersCount++;
                    }

                    // C. Tabungan Lebaran (khusus type tabungan jika ada)
                    if ($pkg->isTabungan()) {
                        $tabunganCustSet[$customer->id] = true;
                        $agentTabunganPackageCounts[$pkg->id] = ($agentTabunganPackageCounts[$pkg->id] ?? 0) + 1;
                        $tabunganPackageTotals[$pkg->id] = ($tabunganPackageTotals[$pkg->id] ?? 0) + 1;
                        $agentTabunganOrdersCount++;
                        $totalTabunganOrdersCount++;
                    }

                    // D. Parsel Eceran (Mingguan Eceran dengan rincian komoditas barang)
                    if ($pkg->isEcer()) {
                        $ecerCustSet[$customer->id] = true;
                        $agentEcerOrdersCount++;
                        $totalEcerOrdersCount++;

                        $customerEcerItems = []; // [itemName => qty]

                        if ($cp->customItems->isNotEmpty()) {
                            foreach ($cp->customItems as $ci) {
                                $itemName = $ci->item ? trim($ci->item->name) : '';
                                if (!empty($itemName)) {
                                    $customerEcerItems[$itemName] = ($customerEcerItems[$itemName] ?? 0) + $ci->quantity;
                                }
                            }
                        } elseif ($pkg->packageItems->isNotEmpty()) {
                            foreach ($pkg->packageItems as $pi) {
                                $itemName = $pi->item ? trim($pi->item->name) : '';
                                if (!empty($itemName)) {
                                    $customerEcerItems[$itemName] = ($customerEcerItems[$itemName] ?? 0) + $pi->quantity;
                                }
                            }
                        } else {
                            $cleanName = preg_replace('/^Paket\s+(Parsel\s+)?Eceran\s+/i', '', trim($pkg->name));
                            $itemName = $cleanName ?: $pkg->name;
                            $customerEcerItems[$itemName] = ($customerEcerItems[$itemName] ?? 0) + 1;
                        }

                        foreach ($customerEcerItems as $itemName => $qty) {
                            $colToUse = $itemName;
                            foreach ($ecerItemColumns as $col) {
                                if (strcasecmp($col, $itemName) === 0 || strcasecmp(preg_replace('/^Paket\s+(Parsel\s+)?Eceran\s+/i', '', $col), preg_replace('/^Paket\s+(Parsel\s+)?Eceran\s+/i', '', $itemName)) === 0) {
                                    $colToUse = $col;
                                    break;
                                }
                            }

                            if (!isset($agentEcerItemCounts[$colToUse])) {
                                $agentEcerItemCounts[$colToUse] = 0;
                                $agentEcerItemQty[$colToUse] = 0;
                            }
                            $agentEcerItemCounts[$colToUse] += 1;
                            $agentEcerItemQty[$colToUse] += $qty;

                            if (!isset($ecerItemTotals[$colToUse])) {
                                $ecerItemTotals[$colToUse] = ['customers' => 0, 'qty' => 0];
                            }
                            $ecerItemTotals[$colToUse]['customers'] += 1;
                            $ecerItemTotals[$colToUse]['qty'] += $qty;
                        }
                    }
                }
            }

            $agent->package_counts = $agentPackageCounts;
            $agent->total_customers = $agent->customers->count();
            $agent->total_orders = $agentOrdersCount;
            $agent->total_target_amount = $agentTargetSum;
            $agent->total_paid_amount = $agentPaidSum;

            // Harian
            $agent->harian_customers_count = count($harianCustSet);
            $agent->harian_package_counts = $agentHarianPackageCounts;
            $agent->total_harian_orders = $agentHarianOrdersCount;

            // Mingguan Lainnya / Standar
            $agent->mingguan_customers_count = count($mingguanCustSet);
            $agent->mingguan_package_counts = $agentMingguanPackageCounts;
            $agent->total_mingguan_orders = $agentMingguanOrdersCount;

            // Tabungan
            $agent->tabungan_customers_count = count($tabunganCustSet);
            $agent->tabungan_package_counts = $agentTabunganPackageCounts;
            $agent->total_tabungan_orders = $agentTabunganOrdersCount;

            // Eceran
            $agent->ecer_customers_count = count($ecerCustSet);
            $agent->ecer_item_counts = $agentEcerItemCounts;
            $agent->ecer_item_qty = $agentEcerItemQty;
            $agent->total_ecer_orders = $agentEcerOrdersCount;

            $totalCustomersCount += $agent->total_customers;
            $totalTargetNominalSum += $agentTargetSum;
            $totalPaidSum += $agentPaidSum;

            $totalHarianCustomersCount += $agent->harian_customers_count;
            $totalMingguanCustomersCount += $agent->mingguan_customers_count;
            $totalEcerCustomersCount += $agent->ecer_customers_count;
            $totalTabunganCustomersCount += $agent->tabungan_customers_count;
        }

        $allAgentsList = User::where('role', 'agent')->orderBy('name')->get();
        $appSetting = CatalogSetting::current();

        return compact(
            'agents',
            'allAgentsList',
            'packages',
            'packageTotals',
            'harianPackages',
            'harianPackageTotals',
            'mingguanPackages',
            'mingguanPackageTotals',
            'tabunganPackages',
            'tabunganPackageTotals',
            'ecerPackages',
            'ecerItemColumns',
            'ecerItemTotals',
            'totalCustomersCount',
            'totalPackagesCount',
            'totalTargetNominalSum',
            'totalPaidSum',
            'totalHarianCustomersCount',
            'totalHarianOrdersCount',
            'totalMingguanCustomersCount',
            'totalMingguanOrdersCount',
            'totalEcerCustomersCount',
            'totalEcerOrdersCount',
            'totalTabunganCustomersCount',
            'totalTabunganOrdersCount',
            'appSetting'
        );
    }
}

