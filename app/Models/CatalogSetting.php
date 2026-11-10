<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogSetting extends Model
{
    use HasFactory;

    protected static ?self $runtimeInstance = null;

    protected static function booted()
    {
        static::saved(function () {
            self::clearCache();
        });
        static::deleted(function () {
            self::clearCache();
        });
    }

    public static function clearCache(): void
    {
        self::$runtimeInstance = null;
        \Illuminate\Support\Facades\Cache::forget('spl_catalog_setting_cached');
    }

    protected $fillable = [
        'app_name',
        'logo_path',
        'owner_name',
        'owner_address',
        'title',
        'program_period',
        'program_start_date',
        'program_end_date',
        'terms_and_conditions',
        'contact_person',
        'bank_account_info',
        'is_book_closed',
        'book_closed_at',
        'book_closed_by',
        'book_closing_notes',
    ];

    protected $casts = [
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'is_book_closed' => 'boolean',
        'book_closed_at' => 'datetime',
    ];

    public function bookClosedByUser()
    {
        return $this->belongsTo(User::class, 'book_closed_by');
    }

    public function isClosedBook(): bool
    {
        return (bool) $this->is_book_closed;
    }

    public function getCalculatedDaysAttribute(): int
    {
        if (!$this->program_start_date || !$this->program_end_date) return 0;
        return max(0, (int) $this->program_start_date->diffInDays($this->program_end_date));
    }

    public function getCalculatedWeeksAttribute(): int
    {
        return (int) floor($this->calculated_days / 7);
    }

    /**
     * Pastikan direktori dan file logo permanen selalu tersedia di public dan storage
     */
    public static function ensurePermanentLogo(): void
    {
        $permDirImages = public_path('images/logo');
        $permDirPublic = public_path('permanent');
        $permDirStorage = storage_path('app/permanent');

        $imagesLogo = public_path('images/logo/logo.png');
        $publicLogo = public_path('permanent/logo.png');
        $storageLogo = storage_path('app/permanent/logo.png');
        $backupLogo = storage_path('app/permanent/logo_master_backup.png');
        $defaultSource = public_path('icon-source.png');

        foreach ([$permDirImages, $permDirPublic, $permDirStorage] as $d) {
            if (!file_exists($d)) {
                @mkdir($d, 0755, true);
            }
        }

        // Cari file sumber logo yang valid
        $masterSource = null;
        if (file_exists($imagesLogo) && filesize($imagesLogo) > 1000) {
            $masterSource = $imagesLogo;
        } elseif (file_exists($storageLogo) && filesize($storageLogo) > 1000) {
            $masterSource = $storageLogo;
        } elseif (file_exists($backupLogo) && filesize($backupLogo) > 1000) {
            $masterSource = $backupLogo;
        } elseif (file_exists($publicLogo) && filesize($publicLogo) > 1000) {
            $masterSource = $publicLogo;
        } elseif (file_exists($defaultSource)) {
            $masterSource = $defaultSource;
        }

        if ($masterSource) {
            if (!file_exists($imagesLogo) || filesize($imagesLogo) < 1000) {
                @copy($masterSource, $imagesLogo);
            }
            if (!file_exists($publicLogo) || filesize($publicLogo) < 1000) {
                @copy($masterSource, $publicLogo);
            }
            if (!file_exists($storageLogo) || filesize($storageLogo) < 1000) {
                @copy($masterSource, $storageLogo);
            }
            if (!file_exists($backupLogo) || filesize($backupLogo) < 1000) {
                @copy($masterSource, $backupLogo);
            }
        }
    }

    /**
     * Accessor logo_path: Jika logo_path kosong atau file fisik hilang,
     * otomatis fallback ke logo di storage permanen ('images/logo/logo.png' atau 'permanent/logo.png')
     */
    public function getLogoPathAttribute($value)
    {
        if ($value && file_exists(public_path($value))) {
            return $value;
        }

        self::ensurePermanentLogo();

        if (file_exists(public_path('images/logo/logo.png'))) {
            return 'images/logo/logo.png';
        }

        if (file_exists(public_path('permanent/logo.png'))) {
            return 'permanent/logo.png';
        }

        return $value ?: 'images/logo/logo.png';
    }

    /**
     * Accessor URL lengkap logo dengan cache-busting timestamp
     */
    public function getLogoUrlAttribute(): string
    {
        $path = $this->logo_path;
        if ($path && file_exists(public_path($path))) {
            return asset($path) . '?v=' . filemtime(public_path($path));
        }

        if (file_exists(public_path('images/logo/logo.png'))) {
            return asset('images/logo/logo.png') . '?v=' . filemtime(public_path('images/logo/logo.png'));
        }

        if (file_exists(public_path('permanent/logo.png'))) {
            return asset('permanent/logo.png') . '?v=' . filemtime(public_path('permanent/logo.png'));
        }

        return asset('icon-source.png');
    }

    public static function current(): self
    {
        if (self::$runtimeInstance !== null) {
            return self::$runtimeInstance;
        }

        $setting = \Illuminate\Support\Facades\Cache::remember('spl_catalog_setting_cached', 300, function () {
            self::ensurePermanentLogo();

            $s = self::first();
            if (!$s) {
                $defaultLogo = file_exists(public_path('permanent/logo.png')) ? 'permanent/logo.png' : null;
                $s = self::create([
                    'app_name' => 'SPL (Susi Parsel Lebaran)',
                    'logo_path' => $defaultLogo,
                    'owner_name' => 'Susi',
                    'owner_address' => 'Jl. Pasar Tradisional No. 88, Blok Melati, Jawa Barat',
                    'title' => 'KATALOG SPL (SUSI PARSEL LEBARAN)',
                    'program_period' => 'Periode Musim Lebaran 1447 H / 2026',
                    'program_start_date' => '2025-08-17',
                    'program_end_date' => '2026-03-15',
                    'terms_and_conditions' => "1. Pembayaran iuran setoran wajib dilakukan setiap minggu sesuai nominal yang disepakati.\n2. Untuk paket tabungan, bonus barang hangus apabila konsumen tidak setor selama 3 minggu berturut-turut.\n3. Apabila konsumen membatalkan pesanan / mundur di tengah jalan, dikenakan biaya administrasi 10% dari total uang yang telah disetor.\n4. Penyerahan parsel dan tabungan dilakukan H-7 sebelum Hari Raya Idul Fitri 1447 H.\n5. Konsumen yang belum lunas dapat memilih penyesuaian barang (Mini-POS) sesuai dengan saldo setoran yang telah masuk.",
                    'contact_person' => '0812-3456-7890 (Susi - Owner SPL)',
                    'bank_account_info' => 'Bank BCA: 123-456-789 a.n. Susi',
                ]);
            } elseif (!$s->logo_path || !file_exists(public_path($s->logo_path))) {
                if (file_exists(public_path('permanent/logo.png'))) {
                    $s->update(['logo_path' => 'permanent/logo.png']);
                }
            }

            return $s;
        });

        self::$runtimeInstance = $setting;
        return $setting;
    }
}

