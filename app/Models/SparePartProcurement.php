<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePartProcurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'procurement_number',
        'procurement_date',
        'supplier_name',
        'branch_id',
        'created_by',
        'status',
        'total_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'procurement_date' => 'date',
            'total_cost' => 'decimal:2',
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = 'PRC-'.date('Ym').'-';
        $latest = static::where('procurement_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $number = 1;
        if ($latest) {
            $parts = explode('-', $latest->procurement_number);
            $number = ((int) end($parts)) + 1;
        }

        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SparePartProcurementItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return 'Rp '.number_format((float) $this->total_cost, 0, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'received' => 'Diterima (Stok Masuk)',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'received' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
