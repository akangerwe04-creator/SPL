<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCustomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_package_id',
        'item_id',
        'quantity',
        'unit',
        'installment_amount',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'installment_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function customerPackage()
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

