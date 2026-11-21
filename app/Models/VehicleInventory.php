<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'branch_id',
        'stock_quantity',
        'reserved_quantity',
    ];

    protected function casts(): array
    {
        return [
            'stock_quantity' => 'integer',
            'reserved_quantity' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->orderByDesc('id');
    }

    /**
     * Hitung stok yang tersedia (fisik - reservasi)
     */
    protected function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn () => max(0, $this->stock_quantity - $this->reserved_quantity)
        );
    }

    /**
     * Label status stok untuk representasi UI
     */
    public function getStockStatusAttribute(): string
    {
        $avail = $this->available_stock;

        if ($avail <= 0) {
            return 'out_of_stock';
        }

        if ($avail <= 2) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Stok Habis',
            'low_stock' => 'Stok Terbatas (Rendah)',
            'in_stock' => 'Tersedia',
            default => 'Tersedia',
        };
    }

    public function getStockStatusBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'bg-rose-50 text-rose-700 border-rose-200',
            'low_stock' => 'bg-amber-50 text-amber-700 border-amber-200',
            'in_stock' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}
