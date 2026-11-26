<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartStockMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'spare_part_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'created_by',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Pengadaan Masuk',
            'out' => 'Pemakaian Servis',
            'return' => 'Pengembalian Stok',
            'adjustment' => 'Penyesuaian Stok',
            default => ucfirst($this->type),
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'in' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'out' => 'bg-rose-50 text-rose-700 border-rose-200',
            'return' => 'bg-sky-50 text-sky-700 border-sky-200',
            'adjustment' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
