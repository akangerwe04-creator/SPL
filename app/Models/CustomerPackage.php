<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'package_id',
        'agent_id',
        'start_date',
        'total_target',
        'total_paid',
        'status', // active, completed, canceled
        'is_bonus_forfeited',
        'forfeit_reason',
        'consecutive_unpaid_weeks',
        'cancellation_fee',
        'refund_amount',
        'fulfillment_status', // pending, full, partial, refund
        'fulfillment_notes',
        'fulfillment_decision', // safe_full, custom_partial, cash_only, pending
        'decided_at',
        'decided_by',
        'packaging_id',
        'packaging_price',
    ];

    protected $casts = [
        'total_target' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'packaging_price' => 'decimal:2',
        'is_bonus_forfeited' => 'boolean',
        'consecutive_unpaid_weeks' => 'integer',
        'start_date' => 'date',
        'decided_at' => 'datetime',
    ];

    protected $appends = [
        'progress_percent',
        'is_safe_reached',
        'recommended_cash',
        'goods_value',
        'package_cash',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isParsel(): bool
    {
        return $this->package?->isParsel() ?? false;
    }

    public function isTabungan(): bool
    {
        return $this->package?->isTabungan() ?? false;
    }

    public function isEcer(): bool
    {
        return $this->package?->isEcer() ?? false;
    }

    public function hasSafeThreshold(): bool
    {
        return $this->package?->hasSafeThreshold() ?? false;
    }

    public function getSafeThresholdAttribute(): ?float
    {
        return $this->package?->safe_threshold;
    }

    public function isSafeThresholdReached(): bool
    {
        $paid = (float) $this->total_paid;
        $target = (float) $this->total_target;

        if ($target > 0 && $paid >= $target) {
            return true;
        }

        if ($this->hasSafeThreshold() && $this->package) {
            return $this->package->isSafeReached($paid);
        }

        return false;
    }

    public function getRecommendedCash(): float
    {
        if (!$this->package) return 0.0;
        return $this->package->getRecommendedCashForCustomer((float)$this->total_paid);
    }

    public function getRecommendedCashAttribute(): float
    {
        return $this->getRecommendedCash();
    }

    public function getIsSafeReachedAttribute(): bool
    {
        return $this->isSafeThresholdReached();
    }

    public function getGoodsValueAttribute(): float
    {
        return $this->package ? (float)$this->package->getTotalGoodsValue() : 0.0;
    }

    public function getPackageCashAttribute(): float
    {
        return $this->package ? (float)$this->package->getCashComponentNominal() : 0.0;
    }

    public function isDecisionMade(): bool
    {
        return !empty($this->fulfillment_decision) || ($this->fulfillment_status && $this->fulfillment_status !== 'pending');
    }

    public function packaging()
    {
        return $this->belongsTo(Packaging::class, 'packaging_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class)->orderBy('period_number', 'asc');
    }

    public function customItems()
    {
        return $this->hasMany(CustomerCustomItem::class)->with('item');
    }

    public function partialFulfillment()
    {
        return $this->hasOne(PartialFulfillment::class)->with('items.item');
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->total_target <= 0) return 0;
        return min(100, round(($this->total_paid / $this->total_target) * 100, 1));
    }

    public function getFormattedPaidAttribute(): string
    {
        return 'Rp ' . number_format($this->total_paid, 0, ',', '.');
    }

    public function getFormattedTargetAttribute(): string
    {
        return 'Rp ' . number_format($this->total_target, 0, ',', '.');
    }

    public function getRemainingNominalAttribute(): float
    {
        return max(0, (float)$this->total_target - (float)$this->total_paid);
    }

    public function getFormattedRemainingAttribute(): string
    {
        return 'Rp ' . number_format($this->getRemainingNominalAttribute(), 0, ',', '.');
    }

    /**
     * Hitung ulang total_paid dari ceklis pembayaran yang sudah disahkan/dibayar
     */
    public function recalculatePaidAmount(): void
    {
        $paid = $this->payments()->where('is_paid', true)->sum('amount');
        $this->total_paid = $paid;

        if ($this->total_paid >= $this->total_target && $this->total_target > 0) {
            $this->status = 'completed';
        }

        $this->evaluateBonusEligibility();
        $this->save();
    }

    /**
     * Evaluasi aturan Tabungan:
     * Bonus hangus jika tidak setor 3 minggu berturut-turut!
     */
    public function evaluateBonusEligibility(): void
    {
        if (!$this->package || !$this->package->isTabungan()) {
            return;
        }

        $payments = $this->payments()->orderBy('period_number', 'asc')->get();
        $consecutiveUnpaid = 0;
        $maxConsecutive = 0;

        foreach ($payments as $p) {
            if (!$p->is_paid) {
                $consecutiveUnpaid++;
                if ($consecutiveUnpaid > $maxConsecutive) {
                    $maxConsecutive = $consecutiveUnpaid;
                }
            } else {
                $consecutiveUnpaid = 0;
            }
        }

        $this->consecutive_unpaid_weeks = $maxConsecutive;

        $unit = ($this->package && $this->package->isHarian()) ? 'hari' : 'minggu';
        if ($maxConsecutive >= 3 && !$this->is_bonus_forfeited) {
            $this->is_bonus_forfeited = true;
            $this->forfeit_reason = "Tidak menyetor 3 {$unit} berturut-turut ({$maxConsecutive} {$unit} bolong). Sesuai aturan SPL, bonus barang hangus.";
        }
    }

    /**
     * Aturan Pembatalan / Mundur di tengah jalan:
     * Dikenakan potongan 10% dari total uang yang telah disetor
     */
    public function cancelMembership(string $reason = ''): void
    {
        $this->status = 'canceled';
        $feeRate = 0.10; // Potongan 10%
        $this->cancellation_fee = round($this->total_paid * $feeRate, 2);
        $this->refund_amount = round($this->total_paid - $this->cancellation_fee, 2);
        $this->fulfillment_status = 'refund';
        $this->fulfillment_notes = "Konsumen berhenti di tengah jalan. Total setor: Rp " . number_format($this->total_paid, 0, ',', '.') . 
            ", Potongan SPL (10%): Rp " . number_format($this->cancellation_fee, 0, ',', '.') . 
            ", Sisa dana dikembalikan: Rp " . number_format($this->refund_amount, 0, ',', '.') . 
            ($reason ? " | Alasan: {$reason}" : '');
        $this->save();
    }
}

