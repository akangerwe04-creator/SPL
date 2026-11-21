<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\CatalogSetting;
use App\Models\PeriodArchive;
use App\Models\User;
use App\Models\Item;
use App\Models\Packaging;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\CustomerCustomItem;
use App\Models\Payment;
use App\Models\AgentDeposit;
use App\Models\PartialFulfillment;
use App\Models\PartialFulfillmentItem;
use App\Models\Post;
use App\Models\Comment;
use App\Models\PostLike;

class PeriodController extends Controller
{
    /**
     * Folder penyimpanan berkas arsip periode lokal
     */
    protected static function getArchiveDir(): string
    {
        $dir = storage_path('app/period_archives');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Halaman Utama Manajemen Periode & Buka Periode Baru (Khusus Administrator)
     */
    public function index()
    {
        $appSetting = CatalogSetting::current();
        $isClosed = (bool) $appSetting->is_book_closed;

        // Hitung statistik data musim saat ini yang siap diarsipkan
        $currentStats = [
            'total_agents' => User::where('role', 'agent')->count(),
            'total_items' => Item::count(),
            'total_packagings' => Packaging::count(),
            'total_packages' => Package::count(),
            'total_customers' => Customer::count(),
            'total_orders' => CustomerPackage::count(),
            'total_target_nominal' => (float) CustomerPackage::sum('total_target'),
            'total_paid_nominal' => (float) CustomerPackage::sum('total_paid'),
        ];

        // Riwayat arsip periode sebelumnya
        $archives = PeriodArchive::latest()->paginate(10);

        // Rekomendasi nama periode berikutnya (e.g. Musim 1448 H / 2027)
        $currentPeriodName = $appSetting->program_period ?: ('Musim ' . date('Y'));
        $suggestedNextPeriod = $this->generateNextPeriodSuggestion($currentPeriodName);

        // Rekomendasi tanggal periode baru
        $suggestedStartDate = now()->format('Y-m-d');
        $suggestedEndDate = now()->addDays(305)->format('Y-m-d');

        $isBookClosed = $isClosed;

        return view('admin.period.index', compact(
            'appSetting',
            'isClosed',
            'isBookClosed',
            'currentStats',
            'archives',
            'currentPeriodName',
            'suggestedNextPeriod',
            'suggestedStartDate',
            'suggestedEndDate'
        ));
    }

    /**
     * Eksekusi Buka Periode Baru: Diarsipkan & Reset Data untuk Input Ulang
     */
    public function openNewPeriod(Request $request)
    {
        $appSetting = CatalogSetting::current();

        // 1. SYARAT MUTLAK: PERIODE SEBELUMNYA WAJIB SUDAH TUTUP BUKU DULU
        if (!$appSetting->is_book_closed) {
            return back()->with('error', 'Gagal membuka periode baru! Periode saat ini wajib Tutup Buku terlebih dahulu sebelum membuka periode baru.');
        }

        // 2. VALIDASI INPUT FORMULIR
        $request->validate([
            'new_period_name' => 'required|string|max:100',
            'new_start_date' => 'required|date',
            'new_end_date' => 'required|date|after_or_equal:new_start_date',
            'confirm_text' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // 3. KONFIRMASI KEAMANAN (Ketik "BUKA PERIODE BARU" atau Password Administrator)
        $confirm = trim($request->confirm_text);
        $isTextValid = (strtoupper($confirm) === 'BUKA PERIODE BARU');
        $isPasswordValid = Hash::check($confirm, auth()->user()->password);

        if (!$isTextValid && !$isPasswordValid) {
            return back()->with('error', 'Konfirmasi keamanan tidak sesuai! Ketik "BUKA PERIODE BARU" atau masukkan kata sandi Administrator Anda.');
        }

        try {
            $periodToArchive = $appSetting->program_period ?: ('Musim ' . date('Y'));
            $archiveDir = self::getArchiveDir();
            $timestamp = date('Ymd_His');
            $safePeriodSlug = Str::slug($periodToArchive, '_');
            $filename = "Arsip_{$safePeriodSlug}_{$timestamp}.json";
            $filePath = $archiveDir . '/' . $filename;

            // 4. LANGKAH 1: ARSIPKAN SELURUH DATA MUSIM BERJALAN SECARA LENGKAP
            $archivePayload = [
                'metadata' => [
                    'archive_title' => "Arsip Resmi Data SPL {$periodToArchive}",
                    'period_name' => $periodToArchive,
                    'program_start_date' => $appSetting->program_start_date?->format('Y-m-d'),
                    'program_end_date' => $appSetting->program_end_date?->format('Y-m-d'),
                    'book_closed_at' => $appSetting->book_closed_at?->toDateTimeString(),
                    'book_closed_by' => $appSetting->bookClosedByUser?->name ?: 'Administrator',
                    'book_closing_notes' => $appSetting->book_closing_notes,
                    'archived_at' => now()->toDateTimeString(),
                    'archived_by' => auth()->user()->name,
                    'admin_notes' => $request->input('notes'),
                ],
                'summary_stats' => [
                    'total_agents' => User::where('role', 'agent')->count(),
                    'total_items' => Item::count(),
                    'total_packagings' => Packaging::count(),
                    'total_packages' => Package::count(),
                    'total_customers' => Customer::count(),
                    'total_orders' => CustomerPackage::count(),
                    'total_target_nominal' => (float) CustomerPackage::sum('total_target'),
                    'total_paid_nominal' => (float) CustomerPackage::sum('total_paid'),
                ],
                'data' => [
                    'agents' => User::where('role', 'agent')->get()->makeHidden(['password', 'remember_token'])->toArray(),
                    'packagings' => Packaging::all()->toArray(),
                    'items' => Item::all()->toArray(),
                    'packages' => Package::with('packageItems.item')->get()->toArray(),
                    'customers' => Customer::with([
                        'customerPackages.package',
                        'customerPackages.packaging',
                        'customerPackages.customItems.item',
                        'customerPackages.payments',
                        'customerPackages.partialFulfillment.items.item',
                    ])->get()->toArray(),
                    'agent_deposits' => AgentDeposit::with('payments')->get()->toArray(),
                    'partial_fulfillments' => PartialFulfillment::with('items.item')->get()->toArray(),
                ],
            ];

            $jsonContent = json_encode($archivePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            File::put($filePath, $jsonContent);
            $fileSize = File::size($filePath);

            // Simpan riwayat arsip ke database
            PeriodArchive::create([
                'period_name' => $periodToArchive,
                'program_start_date' => $appSetting->program_start_date,
                'program_end_date' => $appSetting->program_end_date,
                'book_closed_at' => $appSetting->book_closed_at,
                'book_closed_by_name' => $appSetting->bookClosedByUser?->name ?: auth()->user()->name,
                'total_agents' => $archivePayload['summary_stats']['total_agents'],
                'total_items' => $archivePayload['summary_stats']['total_items'],
                'total_packagings' => $archivePayload['summary_stats']['total_packagings'],
                'total_packages' => $archivePayload['summary_stats']['total_packages'],
                'total_customers' => $archivePayload['summary_stats']['total_customers'],
                'total_orders' => $archivePayload['summary_stats']['total_orders'],
                'total_target_nominal' => $archivePayload['summary_stats']['total_target_nominal'],
                'total_paid_nominal' => $archivePayload['summary_stats']['total_paid_nominal'],
                'archive_filename' => $filename,
                'archive_file_path' => $filePath,
                'archive_file_size' => $fileSize,
                'archived_by_name' => auth()->user()->name,
                'notes' => $request->input('notes') ?: 'Diarsipkan otomatis saat pembukaan periode baru.',
            ]);

            // 5. LANGKAH 2: BERSIHKAN / RESET DATA OPERASIONAL & MASTER DATA UNTUK INPUT BARU
            $isSqlite = (DB::getDriverName() === 'sqlite');
            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            DB::transaction(function () {
                // A. Bersihkan riwayat penyerahan parsel & mini-pos
                PartialFulfillmentItem::query()->delete();
                PartialFulfillment::query()->delete();

                // B. Bersihkan tagihan & setoran kas agen
                Payment::query()->delete();
                AgentDeposit::query()->delete();

                // C. Bersihkan data konsumen & paket yang diambil
                CustomerCustomItem::query()->delete();
                CustomerPackage::query()->delete();
                Customer::query()->delete();

                // D. Bersihkan postingan & feed sosial internal
                $posts = Post::all();
                foreach ($posts as $post) {
                    if ($post->image_path && file_exists(public_path($post->image_path))) {
                        if (!str_contains($post->image_path, 'sample_')) {
                            @unlink(public_path($post->image_path));
                        }
                    }
                }
                if (Schema::hasTable('post_likes')) {
                    PostLike::query()->delete();
                }
                Comment::query()->delete();
                Post::query()->delete();

                // E. Bersihkan menu paket & kemasan & data barang (karena wajib diinput ulang)
                $packages = Package::all();
                foreach ($packages as $pkg) {
                    if ($pkg->image_path && file_exists(public_path($pkg->image_path))) {
                        if (!str_contains($pkg->image_path, 'sample_')) {
                            @unlink(public_path($pkg->image_path));
                        }
                    }
                }
                PackageItem::query()->delete();
                Package::query()->delete();
                Packaging::query()->delete();
                Item::query()->delete();

                // F. Bersihkan seluruh akun agen lapangan (HANYA agen, akun OWNER & ADMIN TETAP UTUH)
                User::whereNotIn('role', ['owner', 'admin'])->delete();
            });

            if ($isSqlite) {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            }

            // 6. LANGKAH 3: BUKA BUKU & PERBARUI PERIODE PADA CATALOG SETTING
            $appSetting->update([
                'program_period' => trim($request->new_period_name),
                'program_start_date' => $request->new_start_date,
                'program_end_date' => $request->new_end_date,
                'is_book_closed' => false, // Buka buku baru!
                'book_closed_at' => null,
                'book_closed_by' => null,
                'book_closing_notes' => null,
            ]);

            CatalogSetting::clearCache();

            return redirect()->route('admin.period.index')
                ->with('success', "PERIODE BARU BERHASIL DIBUKA: \"{$request->new_period_name}\"! Data musim sebelumnya ({$periodToArchive}) telah berhasil diarsipkan dengan aman. Sistem kini bersih dan siap untuk menginput ulang data agen, barang, kemasan, serta menu paket.");

        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan saat membuka periode baru: ' . $e->getMessage());
        }
    }

    /**
     * Unduh Berkas Cadangan Arsip Periode (.json)
     */
    public function downloadArchive(PeriodArchive $archive)
    {
        $filePath = $archive->archive_file_path;
        if (!$filePath || !File::exists($filePath)) {
            // Cek di folder arsip default
            $candidate = self::getArchiveDir() . '/' . $archive->archive_filename;
            if (File::exists($candidate)) {
                $filePath = $candidate;
            }
        }

        if (!$filePath || !File::exists($filePath)) {
            return back()->with('error', 'File arsip tidak ditemukan di server.');
        }

        $fileSize = File::size($filePath);
        $downloadName = $archive->archive_filename ?: ('SPL_Arsip_' . $archive->id . '.json');

        return response()->streamDownload(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, $downloadName, [
            'Content-Type' => 'application/json',
            'Content-Length' => $fileSize,
        ]);
    }

    /**
     * Otomatis membuat saran nama periode berikutnya (e.g. 1447 H / 2026 -> 1448 H / 2027)
     */
    private function generateNextPeriodSuggestion(string $current): string
    {
        $hijri = null;
        $masehi = null;

        // Cari tahun hijriyah (e.g. 1447 H)
        if (preg_match('/(\d{3,4})\s*H/i', $current, $mHijri)) {
            $hijri = (int) $mHijri[1] + 1;
        }

        // Cari tahun masehi 4 digit (20xx)
        if (preg_match('/\b(20\d{2})\b/', $current, $mMasehi)) {
            $masehi = (int) $mMasehi[1] + 1;
        } else {
            $masehi = (int) date('Y') + 1;
        }

        if ($hijri) {
            return "Musim {$hijri} H / {$masehi}";
        }

        return "Musim Tahun {$masehi}";
    }
}
