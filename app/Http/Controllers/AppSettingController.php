<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CatalogSetting;
use App\Models\Payment;
use App\Models\AgentDeposit;
use App\Models\PartialFulfillment;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Package;
use App\Models\Item;
use App\Models\User;
use App\Services\ImageOptimizer;

class AppSettingController extends Controller
{
    /**
     * Tampilkan Pengaturan Aplikasi & Integrasi Reset Data
     */
    public function index()
    {
        $setting = CatalogSetting::current();

        $counts = [
            'transactions' => [
                'payments' => Payment::count(),
                'paid_payments' => Payment::where('is_paid', true)->count(),
                'deposits' => AgentDeposit::count(),
            ],
            'fulfillments' => [
                'total' => PartialFulfillment::count(),
            ],
            'customers' => [
                'customers' => Customer::count(),
                'packages' => CustomerPackage::count(),
            ],
            'social' => [
                'posts' => Post::count(),
                'comments' => Comment::count(),
            ],
            'packages' => [
                'total' => Package::count(),
            ],
            'items' => [
                'total' => Item::count(),
            ],
            'agents' => [
                'total' => User::where('role', 'agent')->count(),
            ],
        ];

        $backups = BackupController::getBackupList();

        return view('owner.settings.index', compact('setting', 'counts', 'backups'));
    }

    /**
     * API Notification & App Badging Count
     */
    public function getBadgeCount()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['count' => 0, 'has_activity' => false, 'feed_unseen' => 0]);
        }

        $cacheKey = "spl_user_badge_{$user->id}";
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 30, function () use ($user) {
            $feedUnseen = $user->getUnseenFeedActivitiesCount();
            $depositCount = 0;

            if ($user->isOwnerOrAdmin()) {
                $depositCount = AgentDeposit::where('status', 'submitted')->count();
            } else {
                $depositCount = AgentDeposit::where('agent_id', $user->id)
                    ->whereIn('status', ['approved', 'rejected'])
                    ->whereDate('updated_at', today())
                    ->count();
            }

            $totalBadge = $depositCount + $feedUnseen;

            return [
                'count' => $totalBadge,
                'feed_unseen' => $feedUnseen,
                'has_activity' => $totalBadge > 0,
            ];
        });

        return response()->json($data);
    }

    /**
     * Perbarui Profil Aplikasi, Nama Usaha, Logo, dan Alamat Owner
     */
    public function updateProfile(Request $request)
    {
        $setting = CatalogSetting::current();

        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'nullable|string|max:1000',
            'contact_person' => 'required|string|max:255',
            'program_period' => 'required|string|max:255',
            'program_start_date' => 'nullable|date',
            'program_end_date' => 'nullable|date|after_or_equal:program_start_date',
            'title' => 'required|string|max:255',
            'bank_account_info' => 'nullable|string|max:500',
            'terms_and_conditions' => 'nullable|string',
            'logo' => 'nullable|image|max:8192',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_logo')) {
            if ($setting->logo_path && file_exists(public_path($setting->logo_path)) && !str_contains($setting->logo_path, 'permanent/')) {
                @unlink(public_path($setting->logo_path));
            }
            $validated['logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            $uploadedLogo = $request->file('logo');

            // 1. Simpan ke storage permanent dan folder khusus images/logo agar logo tersimpan aman dan tidak pernah hilang
            CatalogSetting::ensurePermanentLogo();
            $permImages = public_path('images/logo/logo.png');
            $permPublic = public_path('permanent/logo.png');
            $permStorage = storage_path('app/permanent/logo.png');
            $permBackup = storage_path('app/permanent/logo_master_backup.png');

            @copy($uploadedLogo->getRealPath(), $permImages);
            @copy($uploadedLogo->getRealPath(), $permStorage);
            @copy($uploadedLogo->getRealPath(), $permPublic);
            @copy($uploadedLogo->getRealPath(), $permBackup);

            // 2. Simpan juga salinan melalui ImageOptimizer untuk kelengkapan arsip
            if ($setting->logo_path && file_exists(public_path($setting->logo_path)) && !str_contains($setting->logo_path, 'permanent/') && !str_contains($setting->logo_path, 'images/logo/')) {
                @unlink(public_path($setting->logo_path));
            }
            ImageOptimizer::optimizeAndSave($uploadedLogo, 'logos');

            $validated['logo_path'] = 'images/logo/logo.png';
        }

        $setting->update($validated);

        // Sinkronisasi otomatis aset PWA (icon-192, icon-512, favicon, manifest.json)
        self::syncPwaAssetsAndManifest($setting);

        return redirect()->route('owner.settings.index')->with('success', 'Pengaturan profil usaha dan logo aplikasi berhasil disimpan.');
    }

    /**
     * Endpoint dinamis untuk PWA manifest.json
     */
    public function manifest()
    {
        $setting = CatalogSetting::current();
        $manifest = self::getManifestData($setting);
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, private');
    }

    /**
     * Data array Manifest PWA
     */
    public static function getManifestData(CatalogSetting $setting): array
    {
        $appName = $setting->app_name ?: 'SPL (Susi Parsel Lebaran)';
        $shortName = 'SPL Lebaran';
        if (mb_strlen($appName) <= 12) {
            $shortName = $appName;
        } elseif (stripos($appName, 'SPL') !== false) {
            $shortName = 'SPL Lebaran';
        } else {
            $shortName = mb_substr($appName, 0, 12);
        }
        $ver = file_exists(public_path('icon-192.png')) ? filemtime(public_path('icon-192.png')) : time();

        return [
            'id' => './?source=pwa',
            'name' => $appName,
            'short_name' => $shortName,
            'description' => 'Sistem Informasi Manajemen Parsel & Tabungan Lebaran - ' . $appName,
            'lang' => 'id',
            'start_url' => './',
            'scope' => './',
            'display' => 'standalone',
            'background_color' => '#fff5f8',
            'theme_color' => '#db2777',
            'orientation' => 'portrait-primary',
            'categories' => ['business', 'finance', 'shopping'],
            'icons' => [
                [
                    'src' => './icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => './icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => './icon-maskable-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => './icon-maskable-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ]
            ]
        ];
    }

    /**
     * Sinkronkan logo usaha yang diupload menjadi ikon PWA (192px, 512px, favicon, maskable) dan file manifest.json
     */
    public static function syncPwaAssetsAndManifest(CatalogSetting $setting): void
    {
        $manifestData = self::getManifestData($setting);
        @file_put_contents(public_path('manifest.json'), json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $sourceLogo = null;
        if ($setting->logo_path && file_exists(public_path($setting->logo_path))) {
            $sourceLogo = public_path($setting->logo_path);
        } elseif (file_exists(public_path('permanent/logo.png'))) {
            $sourceLogo = public_path('permanent/logo.png');
        } elseif (file_exists(storage_path('app/permanent/logo.png'))) {
            $sourceLogo = storage_path('app/permanent/logo.png');
        }

        if ($sourceLogo && file_exists($sourceLogo)) {
            self::generateSquareIcon($sourceLogo, public_path('icon-192.png'), 192);
            self::generateSquareIcon($sourceLogo, public_path('icon-512.png'), 512);
            self::generateSquareIcon($sourceLogo, public_path('apple-touch-icon.png'), 180);
            self::generateSquareIcon($sourceLogo, public_path('favicon.png'), 64);
            self::generateMaskableIcon($sourceLogo, public_path('icon-maskable-192.png'), 192);
            self::generateMaskableIcon($sourceLogo, public_path('icon-maskable-512.png'), 512);
        }
    }

    /**
     * Helper membuat ikon PWA maskable dengan safe zone dan latar belakang serasi
     */
    protected static function generateMaskableIcon(string $sourcePath, string $destinationPath, int $size): bool
    {
        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        $srcImage = null;
        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
            $srcImage = @imagecreatefromjpeg($sourcePath);
        } elseif (str_contains($mime, 'png')) {
            $srcImage = @imagecreatefrompng($sourcePath);
        } elseif (str_contains($mime, 'webp')) {
            $srcImage = @imagecreatefromwebp($sourcePath);
        }

        if (!$srcImage) return false;

        $target = imagecreatetruecolor($size, $size);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $bgColor = imagecolorallocate($target, 255, 245, 248); // #fff5f8
        imagefilledrectangle($target, 0, 0, $size, $size, $bgColor);

        $innerSize = (int) round($size * 0.80);
        $offset = (int) round(($size - $innerSize) / 2);

        imagealphablending($target, true);
        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);
        imagecopyresampled($target, $srcImage, $offset, $offset, 0, 0, $innerSize, $innerSize, $srcW, $srcH);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagepng($target, $destinationPath, 9);

        imagedestroy($target);
        imagedestroy($srcImage);
        return true;
    }

    /**
     * Helper membuat ikon PWA persegi proporsional berkualitas tinggi dengan latar transparan / padding rapi
     */
    protected static function generateSquareIcon(string $sourcePath, string $destinationPath, int $size, float $paddingRatio = 0.06): bool
    {
        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        $mime = $info['mime'];
        $srcImage = null;
        if (str_contains($mime, 'jpeg') || str_contains($mime, 'jpg')) {
            $srcImage = @imagecreatefromjpeg($sourcePath);
        } elseif (str_contains($mime, 'png')) {
            $srcImage = @imagecreatefrompng($sourcePath);
        } elseif (str_contains($mime, 'webp')) {
            $srcImage = @imagecreatefromwebp($sourcePath);
        }

        if (!$srcImage) return false;

        $srcW = imagesx($srcImage);
        $srcH = imagesy($srcImage);

        $target = imagecreatetruecolor($size, $size);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 255, 255, 255, 127);
        imagefilledrectangle($target, 0, 0, $size, $size, $transparent);
        imagealphablending($target, true);

        $padding = (int) round($size * $paddingRatio);
        $avail = $size - (2 * $padding);
        $scale = min($avail / $srcW, $avail / $srcH);
        $dstW = (int) round($srcW * $scale);
        $dstH = (int) round($srcH * $scale);
        $dstX = (int) round(($size - $dstW) / 2);
        $dstY = (int) round(($size - $dstH) / 2);

        imagecopyresampled($target, $srcImage, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagepng($target, $destinationPath, 9);

        imagedestroy($target);
        imagedestroy($srcImage);
        return true;
    }
}

