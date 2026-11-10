<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'unit_price',
        'base_unit',
        'wholesale_unit',
        'conversion_rate',
        'cost_price',
        'category',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class);
    }

    public function customItems()
    {
        return $this->hasMany(CustomerCustomItem::class);
    }

    public function isUang(): bool
    {
        return strcasecmp($this->category ?? '', 'Uang') === 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    /**
     * Konversi total kuantitas dasar ke satuan grosir + sisa ecer
     * Contoh: 300 botol dengan conversion_rate 24
     * = 12 dus + 12 botol (atau 12.5 dus)
     */
    public function formatWholesaleBreakdown(float $totalBaseQuantity): array
    {
        if ($this->isUang()) {
            return [
                'total_base' => $totalBaseQuantity,
                'wholesale_count' => (int) $totalBaseQuantity,
                'remainder_base' => 0,
                'fractional' => $totalBaseQuantity,
                'text' => number_format($totalBaseQuantity) . ' ' . ($this->base_unit ?: 'amplop'),
            ];
        }

        $rate = $this->conversion_rate > 0 ? (float) $this->conversion_rate : 1;
        $wholesaleCount = floor($totalBaseQuantity / $rate);
        $remainder = $totalBaseQuantity - ($wholesaleCount * $rate);
        $fractionalWholesale = round($totalBaseQuantity / $rate, 2);

        return [
            'total_base' => $totalBaseQuantity,
            'wholesale_count' => (int) $wholesaleCount,
            'remainder_base' => (int) $remainder,
            'fractional' => $fractionalWholesale,
            'text' => $wholesaleCount . ' ' . $this->wholesale_unit . ($remainder > 0 ? ' + ' . $remainder . ' ' . $this->base_unit : ''),
        ];
    }

    /**
     * Generate kode unik untuk item barang atau uang.
     * Dijamin tidak akan pernah duplikat meskipun ada data yang dihapus atau dibuat berurutan.
     */
    public static function generateNextCode(?string $category = null, ?string $prefix = null): string
    {
        $isUang = $category !== null && strcasecmp($category, 'Uang') === 0;

        if ($isUang) {
            $prefix = 'UANG-';
        } elseif ($prefix === null) {
            $prefix = ($category === 'Hadiah Bonus') ? ('BNS-' . date('ym') . '-') : ('BRG-' . date('ym') . '-');
        }

        // Ambil semua kode yang diawali prefix untuk mencari angka sequence tertinggi
        $existingCodes = self::where('code', 'like', $prefix . '%')->pluck('code');
        $maxNum = 0;
        foreach ($existingCodes as $c) {
            if (preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $c, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        // Jika tidak ada dengan prefix format tanggal, cek juga jika ada kode legacy BRG-001
        if ($maxNum === 0 && str_starts_with($prefix, 'BRG-')) {
            $legacyCodes = self::where('code', 'like', 'BRG-%')->pluck('code');
            foreach ($legacyCodes as $c) {
                if (preg_match('/BRG-(?:(?:\d{4}-)?0*)(\d+)/', $c, $m)) {
                    $num = (int)$m[1];
                    if ($num > $maxNum) {
                        $maxNum = $num;
                    }
                }
            }
        }

        $candidateNum = max(1, $maxNum + 1);
        do {
            $code = $prefix . str_pad($candidateNum, 3, '0', STR_PAD_LEFT);
            $candidateNum++;
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
