<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    /**
     * Folder penyimpanan file cadangan lokal
     */
    protected static function getBackupDir(): string
    {
        $dir = storage_path('app/backups');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Dapatkan daftar file cadangan yang tersimpan di server
     */
    public static function getBackupList(): array
    {
        $dir = self::getBackupDir();
        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            if (in_array(strtolower($file->getExtension()), ['sqlite', 'db', 'sqlite3'])) {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => self::formatBytes($file->getSize()),
                    'raw_size' => $file->getSize(),
                    'created_at' => date('d M Y, H:i:s', $file->getMTime()),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // Urutkan dari yang terbaru
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return $backups;
    }

    /**
     * Unduh file database saat ini secara langsung (1-Click Instant Download)
     */
    public function downloadCurrent()
    {
        $dbPath = database_path('database.sqlite');
        if (!File::exists($dbPath)) {
            return back()->with('error', 'File database tidak ditemukan.');
        }

        $timestamp = date('Y-m-d_His');
        $filename = "SPL_Backup_{$timestamp}.sqlite";
        $fileSize = File::size($dbPath);

        return response()->streamDownload(function () use ($dbPath) {
            $stream = fopen($dbPath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, $filename, [
            'Content-Type' => 'application/x-sqlite3',
            'Content-Length' => $fileSize,
        ]);
    }

    /**
     * Buat snapshot cadangan baru dan simpan di server
     */
    public function createSnapshot()
    {
        $dbPath = database_path('database.sqlite');
        if (!File::exists($dbPath)) {
            return back()->with('error', 'File database utama tidak ditemukan.');
        }

        $dir = self::getBackupDir();
        $timestamp = date('Y-m-d_His');
        $dest = $dir . "/SPL_Backup_{$timestamp}.sqlite";

        File::copy($dbPath, $dest);

        return redirect()->route('owner.settings.index', ['tab' => 'backup'])
            ->with('success', "Cadangan database berhasil dibuat: SPL_Backup_{$timestamp}.sqlite");
    }

    /**
     * Unduh salah satu file cadangan yang tersimpan di server
     */
    public function downloadFile(string $filename)
    {
        $safeName = basename($filename);
        $filePath = self::getBackupDir() . '/' . $safeName;

        if (!File::exists($filePath)) {
            return back()->with('error', 'File cadangan tidak ditemukan.');
        }

        $fileSize = File::size($filePath);

        return response()->streamDownload(function () use ($filePath) {
            $stream = fopen($filePath, 'rb');
            fpassthru($stream);
            fclose($stream);
        }, $safeName, [
            'Content-Type' => 'application/x-sqlite3',
            'Content-Length' => $fileSize,
        ]);
    }

    /**
     * Pulihkan database dari file cadangan yang diunggah atau yang tersimpan
     */
    public function restore(Request $request)
    {
        $dbPath = database_path('database.sqlite');
        $sourcePath = null;

        if ($request->hasFile('backup_file')) {
            $file = $request->file('backup_file');
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, ['sqlite', 'db', 'sqlite3'])) {
                return back()->with('error', 'Format file tidak didukung. Harap unggah file database berformat .sqlite, .db, atau .sqlite3');
            }
            $sourcePath = $file->getRealPath();
        } elseif ($request->filled('saved_filename')) {
            $safeName = basename($request->input('saved_filename'));
            $candidate = self::getBackupDir() . '/' . $safeName;
            if (File::exists($candidate)) {
                $sourcePath = $candidate;
            }
        }

        if (!$sourcePath || !File::exists($sourcePath)) {
            return back()->with('error', 'Silakan pilih atau unggah file cadangan database yang valid.');
        }

        // Verifikasi header SQLite (16 byte pertama: "SQLite format 3\0")
        $fp = fopen($sourcePath, 'rb');
        $header = fread($fp, 16);
        fclose($fp);

        if (strncmp($header, "SQLite format 3", 15) !== 0) {
            return back()->with('error', 'File yang diunggah bukan file SQLite yang valid atau file rusak.');
        }

        // Tutup koneksi PDO SQLite sementara agar file tidak terkunci di OS Windows saat disalin
        DB::disconnect();

        // Safeguard: Buat cadangan otomatis sebelum ditimpa agar data sebelumnya tidak pernah hilang
        if (File::exists($dbPath)) {
            $preRestoreName = self::getBackupDir() . '/Auto_Pre_Restore_' . date('Y-m-d_His') . '.sqlite';
            File::copy($dbPath, $preRestoreName);
        }

        // Lakukan pemulihan (timpa database aktif dengan file cadangan)
        File::copy($sourcePath, $dbPath);

        // Sambungkan kembali koneksi database
        DB::reconnect();

        // Otomatis jalankan migrasi jika database cadangan berasal dari versi struktur sebelumnya
        Artisan::call('migrate', ['--force' => true]);

        // Pastikan akun owner permanen tetap utuh dan aktif setelah pemulihan database
        \App\Models\User::ensurePermanentOwner();

        // Pastikan logo permanen tetap tersambung setelah pemulihan database
        \App\Models\CatalogSetting::ensurePermanentLogo();
        $setting = \App\Models\CatalogSetting::first();
        if ($setting && (!$setting->logo_path || !file_exists(public_path($setting->logo_path)))) {
            if (file_exists(public_path('permanent/logo.png'))) {
                $setting->update(['logo_path' => 'permanent/logo.png']);
            }
        }
        if ($setting) {
            \App\Http\Controllers\AppSettingController::syncPwaAssetsAndManifest($setting);
        }

        return redirect()->route('owner.settings.index', ['tab' => 'backup'])
            ->with('success', 'Database berhasil dipulihkan dari file cadangan. Semua data telah disinkronkan kembali.');
    }

    /**
     * Hapus file cadangan dari server
     */
    public function destroy(string $filename)
    {
        $safeName = basename($filename);
        $filePath = self::getBackupDir() . '/' . $safeName;

        if (File::exists($filePath)) {
            File::delete($filePath);
            return redirect()->route('owner.settings.index', ['tab' => 'backup'])
                ->with('success', "File cadangan {$safeName} berhasil dihapus.");
        }

        return back()->with('error', 'File tidak ditemukan.');
    }

    /**
     * Helper format ukuran file
     */
    protected static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
