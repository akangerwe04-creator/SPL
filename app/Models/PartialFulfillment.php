<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartialFulfillment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_package_id',
        'total_paid',
        'admin_fee',
        'settlement_type', // 'goods' (barang) or 'cash' (tabungan)
        'cash_payout',     // nominal yang diterima konsumen tabungan
        'total_items_value',
        'remaining_refund',
        'adjusted_by',
        'notes',
    ];

    protected $casts = [
        'total_paid' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'cash_payout' => 'decimal:2',
        'total_items_value' => 'decimal:2',
        'remaining_refund' => 'decimal:2',
    ];

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function adjuster()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function items()
    {
        return $this->hasMany(PartialFulfillmentItem::class);
    }

    public function isCashSettlement(): bool
    {
        return $this->settlement_type === 'cash';
    }

    public function isGoodsSettlement(): bool
    {
        return $this->settlement_type === 'goods';
    }

    public function getFormattedPaidAttribute(): string
    {
        return 'Rp ' . number_format($this->total_paid, 0, ',', '.');
    }

    public function getFormattedAdminFeeAttribute(): string
    {
        return 'Rp ' . number_format($this->admin_fee, 0, ',', '.');
    }

    public function getFormattedCashPayoutAttribute(): string
    {
        return 'Rp ' . number_format($this->cash_payout, 0, ',', '.');
    }

    public function getFormattedItemsValueAttribute(): string
    {
        return 'Rp ' . number_format($this->total_items_value, 0, ',', '.');
    }

    public function getFormattedRemainingRefundAttribute(): string
    {
        return 'Rp ' . number_format($this->remaining_refund, 0, ',', '.');
    }
}

