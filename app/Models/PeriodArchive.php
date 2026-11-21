<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_name',
        'program_start_date',
        'program_end_date',
        'book_closed_at',
        'book_closed_by_name',
        'total_agents',
        'total_items',
        'total_packagings',
        'total_packages',
        'total_customers',
        'total_orders',
        'total_target_nominal',
        'total_paid_nominal',
        'archive_filename',
        'archive_file_path',
        'archive_file_size',
        'archived_by_name',
        'notes',
    ];

    protected $casts = [
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'book_closed_at' => 'datetime',
        'total_agents' => 'integer',
        'total_items' => 'integer',
        'total_packagings' => 'integer',
        'total_packages' => 'integer',
        'total_customers' => 'integer',
        'total_orders' => 'integer',
        'total_target_nominal' => 'decimal:2',
        'total_paid_nominal' => 'decimal:2',
        'archive_file_size' => 'integer',
    ];

    public function getFormattedTargetNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_target_nominal, 0, ',', '.');
    }

    public function getFormattedPaidNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_paid_nominal, 0, ',', '.');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->archive_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
