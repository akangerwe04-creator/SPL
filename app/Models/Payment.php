<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_package_id',
        'agent_deposit_id',
        'agent_id',
        'period_number',
        'amount',
        'is_paid',
        'paid_at',
        'is_locked',
        'status', // unpaid, pending, verified
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_locked' => 'boolean',
        'paid_at' => 'datetime',
        'period_number' => 'integer',
    ];

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function deposit()
    {
        return $this->belongsTo(AgentDeposit::class, 'agent_deposit_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Label periode dinamis (Hari ke-X atau Minggu ke-X)
     */
    public function getPeriodLabelAttribute(): string
    {
        $unit = ($this->customerPackage?->package?->isHarian()) ? 'Hari' : 'Minggu';
        return $unit . ' ke-' . $this->period_number;
    }

    /**
     * Format periode singkat (Hari X atau Minggu X)
     */
    public function getPeriodBadgeAttribute(): string
    {
        $unit = ($this->customerPackage?->package?->isHarian()) ? 'Hari' : 'Minggu';
        return $unit . ' ' . $this->period_number;
    }

    /**
     * Apakah pembayaran ini untuk paket harian
     */
    public function isHarian(): bool
    {
        return (bool) ($this->customerPackage?->package?->isHarian());
    }

    /**
     * Agen men-ceklis atau membatalkan ceklis (hanya jika belum disahkan owner)
     */
    public function toggleChecklist(int $agentId): array
    {
        if ($this->customerPackage?->stop_status === 'pending') {
            return [
                'success' => false,
                'message' => 'Paket konsumen ini sedang dalam pengajuan Stop Konsumen dan menunggu konfirmasi Owner. Ceklis dibekukan sementara.',
            ];
        }

        if ($this->customerPackage?->stop_status === 'approved') {
            return [
                'success' => false,
                'message' => 'Konsumen ini telah resmi berhenti (di-ACC Owner). Ceklis setoran tidak dapat diubah.',
            ];
        }

        if ($this->is_locked || $this->status === 'verified' || ($this->deposit && $this->deposit->status === 'approved')) {
            return [
                'success' => false,
                'message' => 'Ceklis ini telah disahkan oleh Owner dan terkunci permanen.',
            ];
        }

        if ($this->deposit && $this->deposit->status === 'submitted') {
            return [
                'success' => false,
                'message' => 'Tagihan ini ada dalam berkas setoran yang sedang diajukan dan menunggu validasi Owner.',
            ];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($agentId) {
            if (!$this->is_paid) {
                // Ceklis kotak: Masukkan ke keranjang setoran aktif agen yang Pending
                $cart = AgentDeposit::getOrCreateActiveCart($agentId);

                $this->is_paid = true;
                $this->paid_at = now();
                $this->agent_id = $agentId;
                $this->agent_deposit_id = $cart->id;
                $this->status = 'pending';
                $this->save();

                // Akumulasi total keranjang
                $cart->recalculateTotal();

                // Perbarui data paket konsumen & cek aturan bonus
                $this->customerPackage->recalculatePaidAmount();

                $periodUnit = ($this->customerPackage?->package?->isHarian()) ? 'Hari' : 'Minggu';

                return [
                    'success' => true,
                    'action' => 'checked',
                    'cart_total' => $cart->total_amount,
                    'formatted_cart_total' => $cart->formatted_total,
                    'customer_paid' => $this->customerPackage->total_paid,
                    'formatted_customer_paid' => $this->customerPackage->formatted_paid,
                    'customer_progress' => $this->customerPackage->progress_percent,
                    'message' => 'Tagihan ' . $periodUnit . ' ke-' . $this->period_number . ' berhasil diceklis dan masuk ke keranjang setoran.',
                ];
            } else {
                // Batalkan ceklis jika status belum verified / locked
                $cart = $this->deposit;

                $this->is_paid = false;
                $this->paid_at = null;
                $this->agent_deposit_id = null;
                $this->status = 'unpaid';
                $this->save();

                if ($cart) {
                    $cart->recalculateTotal();
                }

                $this->customerPackage->recalculatePaidAmount();

                $periodUnit = ($this->customerPackage?->package?->isHarian()) ? 'Hari' : 'Minggu';

                return [
                    'success' => true,
                    'action' => 'unchecked',
                    'cart_total' => $cart ? $cart->total_amount : 0,
                    'formatted_cart_total' => $cart ? $cart->formatted_total : 'Rp 0',
                    'customer_paid' => $this->customerPackage->total_paid,
                    'formatted_customer_paid' => $this->customerPackage->formatted_paid,
                    'customer_progress' => $this->customerPackage->progress_percent,
                    'message' => 'Ceklis ' . $periodUnit . ' ke-' . $this->period_number . ' dibatalkan.',
                ];
            }
        });
    }
}

