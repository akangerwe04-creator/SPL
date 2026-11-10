<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'deposit_number',
        'agent_id',
        'total_amount',
        'status', // pending, submitted, approved, rejected
        'agent_notes',
        'owner_notes',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Dapatkan atau buat keranjang pending aktif agen
     */
    public static function getOrCreateActiveCart(int $agentId): self
    {
        $deposit = self::where('agent_id', $agentId)
            ->where('status', 'pending')
            ->first();

        if (!$deposit) {
            $deposit = self::create([
                'deposit_number' => self::generateNextDepositNumber(),
                'agent_id' => $agentId,
                'total_amount' => 0,
                'status' => 'pending',
            ]);
        }

        return $deposit;
    }

    public static function generateNextDepositNumber(): string
    {
        $prefix = 'DEP-' . date('Ym') . '-';
        $existing = self::where('deposit_number', 'like', $prefix . '%')->pluck('deposit_number');
        $maxNum = 0;
        foreach ($existing as $c) {
            if (preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $c, $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $candidateNum = max(1, $maxNum + 1);
        do {
            $code = $prefix . str_pad($candidateNum, 4, '0', STR_PAD_LEFT);
            $candidateNum++;
        } while (self::where('deposit_number', $code)->exists());

        return $code;
    }

    /**
     * Hitung ulang total akumulasi uang dalam keranjang
     */
    public function recalculateTotal(): void
    {
        $this->total_amount = $this->payments()->where('is_paid', true)->sum('amount');
        $this->save();
    }

    /**
     * Agen mengajukan setoran ke Owner
     */
    public function submit(?string $notes = null): void
    {
        $this->recalculateTotal();
        $this->status = 'submitted';
        $this->agent_notes = $notes;
        $this->submitted_at = now();
        $this->save();
    }

    /**
     * Owner mengesahkan / memvalidasi setoran agen:
     * Mengunci permanen seluruh ceklis pembayaran di keranjang ini.
     */
    public function approve(int $ownerId, ?string $notes = null): void
    {
        $this->status = 'approved';
        $this->approved_by = $ownerId;
        $this->approved_at = now();
        $this->owner_notes = $notes;
        $this->save();

        // Kunci seluruh pembayaran di setoran ini secara eksplisit dan set status verified
        Payment::where('agent_deposit_id', $this->id)->update([
            'is_locked' => true,
            'status' => 'verified',
        ]);

        // Pastikan customer package terkait memperbarui total dibayar dan bonus
        $customerPackageIds = Payment::where('agent_deposit_id', $this->id)
            ->pluck('customer_package_id')
            ->unique();

        foreach ($customerPackageIds as $pkgId) {
            $pkg = CustomerPackage::find($pkgId);
            if ($pkg) {
                $pkg->recalculatePaidAmount();
            }
        }
    }

    /**
     * Owner menolak setoran (misal uang fisik kurang)
     */
    public function reject(int $ownerId, string $notes): void
    {
        $this->status = 'rejected';
        $this->approved_by = $ownerId;
        $this->owner_notes = $notes;
        $this->save();
    }
}

