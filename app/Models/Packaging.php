<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Packaging extends Model
{
    use HasFactory;

    protected $table = 'packagings';

    protected $fillable = [
        'code',
        'name',
        'cost_price',
        'price',
        'image_path',
        'description',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($pkg) {
            if (empty($pkg->code)) {
                $pkg->code = static::generateNextCode();
            }
        });
    }

    public static function generateNextCode(): string
    {
        $prefix = 'KMS-';
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

        $candidateNum = max(1, $maxNum + 1);
        do {
            $code = $prefix . str_pad($candidateNum, 3, '0', STR_PAD_LEFT);
            $candidateNum++;
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_packagings')
            ->withPivot(['id', 'is_default', 'custom_price'])
            ->withTimestamps();
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class, 'packaging_id');
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getFormattedCostPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->cost_price, 0, ',', '.');
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }
        return '';
    }
}

