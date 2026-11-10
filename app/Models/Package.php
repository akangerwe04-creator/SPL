<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type', // parsel, tabungan, ecer
        'base_price', // Harga dasar / pokok paket sebelum admin fee
        'target_nominal',
        'safe_threshold', // Titik Aman Full Parsel SPL (Nominal minimal setoran konsumen untuk jaminan full barang parsel)
        'installment_count',
        'installment_amount',
        'admin_fee',
        'period_type',
        'start_date',
        'end_date',
        'description',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'target_nominal' => 'decimal:2',
        'safe_threshold' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'installment_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'safe_period_count',
        'safe_period_unit',
        'safe_threshold_nominal',
        'formatted_safe_threshold',
        'cash_component_nominal',
        'total_goods_value',
    ];

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'package_items')
            ->withPivot(['id', 'quantity', 'is_bonus'])
            ->withTimestamps();
    }

    public function regularItems()
    {
        return $this->packageItems()->where('is_bonus', false)->with('item');
    }

    public function bonusItems()
    {
        return $this->packageItems()->where('is_bonus', true)->with('item');
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }

    public function packagings()
    {
        return $this->belongsToMany(Packaging::class, 'package_packagings')
            ->withPivot(['id', 'is_default', 'custom_price'])
            ->withTimestamps();
    }

    public function isParsel(): bool
    {
        return $this->type === 'parsel';
    }

    public function isTabungan(): bool
    {
        return $this->type === 'tabungan';
    }

    public function isEcer(): bool
    {
        return $this->type === 'ecer';
    }

    public function isHarian(): bool
    {
        return $this->period_type === 'harian';
    }

    public function isMingguan(): bool
    {
        return $this->period_type === 'mingguan' || empty($this->period_type);
    }

    public function isMingguanLainnya(): bool
    {
        return $this->isMingguan() && !$this->isEcer();
    }

    public function isMingguanEcer(): bool
    {
        return $this->isMingguan() && $this->isEcer();
    }

    public function getPeriodLabelAttribute(): string
    {
        return $this->isHarian() ? 'Hari' : 'Minggu';
    }

    public function getInstallmentLabelAttribute(): string
    {
        return $this->isHarian() ? 'Harian' : 'Mingguan';
    }

    public function getCalculatedDaysAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) return 0;
        return max(0, (int) $this->start_date->diffInDays($this->end_date));
    }

    public function getCalculatedWeeksAttribute(): int
    {
        return (int) floor($this->calculated_days / 7);
    }

    public function getCalculatedRemainderDaysAttribute(): int
    {
        return (int) ($this->calculated_days % 7);
    }

    public function getFormattedDateRangeAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) return '-';
        return $this->start_date->format('d/m/Y') . ' s/d ' . $this->end_date->format('d/m/Y');
    }

    public function getBasePriceAttribute(): float
    {
        if (isset($this->attributes['base_price']) && $this->attributes['base_price'] !== null && (float)$this->attributes['base_price'] > 0) {
            return (float) $this->attributes['base_price'];
        }
        $target = (float) ($this->target_nominal ?? 0);
        $fee = (float) ($this->admin_fee ?? 0);
        return max(0, $target - $fee);
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->base_price, 0, ',', '.');
    }

    public function getFormattedTargetAttribute(): string
    {
        return 'Rp ' . number_format($this->target_nominal, 0, ',', '.');
    }

    public function getFormattedInstallmentAttribute(): string
    {
        return 'Rp ' . number_format($this->installment_amount, 0, ',', '.');
    }

    public function getFormattedAdminFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->admin_fee ?: 0, 0, ',', '.');
    }

    public function hasSafeThreshold(): bool
    {
        return $this->safe_threshold !== null && (float)$this->safe_threshold > 0;
    }

    /**
     * Hitung total nilai nominal uang tunai yang dijanjikan dalam paket ini
     */
    public function getCashComponentNominal(): float
    {
        $cash = 0;
        foreach ($this->packageItems as $pi) {
            $item = $pi->item;
            if (!$item) continue;
            
            $qty = max(1, (int)$pi->quantity);
            if ($item->isUang() && (float)$item->unit_price > 0) {
                $cash += (float)$item->unit_price * $qty;
            } elseif (preg_match('/(?:uang\s*tunai|uang|cash)[^\d]*([\d\.,]+)/i', $item->name, $m)) {
                $cleaned = preg_replace('/[^\d]/', '', $m[1]);
                $nom = (float) $cleaned;
                if ($nom > 0) {
                    $cash += $nom * $qty;
                }
            }
        }

        // Khusus paket tipe tabungan murni jika tidak ada item uang eksplisit
        if ($cash <= 0 && $this->isTabungan()) {
            $cash = (float) ($this->base_price ?: $this->target_nominal);
        }

        return (float) $cash;
    }

    public function getCashComponentNominalAttribute(): float
    {
        return $this->getCashComponentNominal();
    }

    /**
     * Total Harga Semua Barang Isi Paket
     * Rumus: Total Target Paket dikurangi Nilai Uang Tunai dalam Paket
     */
    public function getTotalGoodsValue(): float
    {
        $target = (float) ($this->target_nominal ?: $this->base_price);
        $cash = $this->getCashComponentNominal();
        return max(0, $target - $cash);
    }

    public function getTotalGoodsValueAttribute(): float
    {
        return $this->getTotalGoodsValue();
    }

    /**
     * Rekomendasi Uang Tunai Konsumen saat Tutup Buku
     * Rumus: Dari total setoran konsumen yang masuk, kurangi harga semua barang paket.
     * Sisa uang setoran itulah yang menjadi uang tunai (jika ada uang tunai di paketnya).
     * Contoh: Paket 610rb (uang 350rb, barang 260rb). Konsumen setor 266rb -> uang tunai: 6.000.
     */
    public function getRecommendedCashForCustomer(float $totalPaid): float
    {
        $cashInPackage = $this->getCashComponentNominal();
        if ($cashInPackage <= 0) {
            return 0.0;
        }

        $goodsValue = $this->getTotalGoodsValue();
        $remaining = max(0, $totalPaid - $goodsValue);
        return (float) min($remaining, $cashInPackage);
    }

    /**
     * Titik Aman dinyatakan dalam Frekuensi / Jumlah Setoran (Hari untuk harian, Minggu untuk mingguan)
     */
    public function getSafePeriodCountAttribute(): int
    {
        if (!$this->hasSafeThreshold()) return 0;
        $val = (float) $this->safe_threshold;
        // Jika angka terlalu besar (misal input legacy nominal Rupiah), konversikan ke jumlah hari/minggu
        if ($val > ($this->installment_count ?: 365) && (float)$this->installment_amount > 0) {
            return (int) round($val / (float)$this->installment_amount);
        }
        return (int) round($val);
    }

    public function getSafePeriodUnitAttribute(): string
    {
        return $this->period_type === 'mingguan' ? 'Minggu' : 'Hari';
    }

    public function getSafeThresholdNominalAttribute(): float
    {
        if (!$this->hasSafeThreshold()) return 0.0;
        return (float) ($this->safe_period_count * (float)$this->installment_amount);
    }

    public function getFormattedSafeThresholdAttribute(): string
    {
        if (!$this->hasSafeThreshold()) {
            return 'Tanpa Titik Aman';
        }
        $count = $this->safe_period_count;
        $unit = $this->safe_period_unit;
        $nominal = $this->safe_threshold_nominal;
        return "{$count} {$unit} (Rp " . number_format($nominal, 0, ',', '.') . ")";
    }

    public function isSafeReached(float $totalPaid): bool
    {
        // Kondisi Lunas
        $target = (float) ($this->target_nominal ?: $this->base_price);
        if ($target > 0 && $totalPaid >= $target) {
            return true;
        }

        // Kondisi Titik Aman (Berdasarkan jumlah hari/minggu yang setara nominal)
        if ($this->hasSafeThreshold()) {
            $needed = $this->safe_threshold_nominal;
            return $totalPaid >= $needed;
        }

        return false;
    }

    public function calculateTotalRetailValue(): float
    {
        $total = 0;
        foreach ($this->packageItems as $pi) {
            if ($pi->item) {
                $total += ($pi->item->unit_price * $pi->quantity);
            }
        }
        return (float) $total;
    }

    /**
     * Pembulatan Cicilan sesuai arahan Owner SPL:
     * Jika 3 digit di belakangnya kurang dari atau sama dengan 500 (misal 7266, 7001, 7499, 7500)
     * maka dibulatkan ke 500 (misal 7266 -> 7500).
     * Jika lebih dari 500 (misal 7550, 7501, 7999) maka dibulatkan ke 1000 (misal 7550 -> 8000).
     * Jika tepat 000 (kelipatan 1000 pas) maka tetap.
     */
    public static function roundInstallment(float $amount): float
    {
        $intVal = (int) ceil($amount);
        if ($intVal <= 0) {
            return 0.0;
        }

        $rem = $intVal % 1000;
        if ($rem === 0) {
            return (float) $intVal;
        }

        $base = (int) (floor($intVal / 1000) * 1000);
        if ($rem <= 500) {
            return (float) ($base + 500);
        }

        return (float) ($base + 1000);
    }

    /**
     * Generate kode unik untuk Paket (PAR-xxx, TAB-xxx, ECE-xxx)
     * Dijamin tidak akan pernah duplikat meskipun ada data yang dihapus.
     */
    public static function generateNextCode(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3)); // PAR, TAB, ECE
        $prefixWithHyphen = $prefix . '-';

        $existingCodes = self::where('code', 'like', $prefixWithHyphen . '%')->pluck('code');
        $maxNum = 0;
        foreach ($existingCodes as $c) {
            if (preg_match('/' . preg_quote($prefixWithHyphen, '/') . '(\d+)/', $c, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $candidateNum = max(1, $maxNum + 1);
        do {
            $code = $prefixWithHyphen . str_pad($candidateNum, 3, '0', STR_PAD_LEFT);
            $candidateNum++;
        } while (self::where('code', $code)->exists());

        return $code;
    }
}

