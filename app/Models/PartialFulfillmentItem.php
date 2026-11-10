<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartialFulfillmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'partial_fulfillment_id',
        'item_id',
        'quantity',
        'unit_price',
        'subtotal',
        'is_bonus',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_bonus' => 'boolean',
    ];

    public function partialFulfillment()
    {
        return $this->belongsTo(PartialFulfillment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

