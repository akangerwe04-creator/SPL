<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'code',
        'name',
        'phone',
        'address',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function customerPackages()
    {
        return $this->hasMany(CustomerPackage::class);
    }

    public function activePackage()
    {
        return $this->hasOne(CustomerPackage::class)->where('status', 'active')->latestOfMany();
    }

    public static function generateNextCode(): string
    {
        $prefix = 'KNS-' . date('ym') . '-';
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
}

