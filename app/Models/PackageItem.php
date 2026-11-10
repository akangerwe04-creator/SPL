<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'item_id',
        'quantity',
        'unit',
        'installment_amount',
        'is_bonus',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'installment_amount' => 'decimal:2',
        'is_bonus' => 'boolean',
    ];

    public function getFormattedInstallmentAttribute(): string
    {
        return 'Rp ' . number_format($this->installment_amount ?: 0, 0, ',', '.');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

