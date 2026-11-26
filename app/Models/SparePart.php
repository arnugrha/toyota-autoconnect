<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'oil_fluids' => 'Oli & Fluida',
        'filters' => 'Filter (Oli, Udara, AC)',
        'brakes' => 'Sistem Pengereman',
        'electrical' => 'Kelistrikan & Aki',
        'suspension' => 'Kaki-Kaki & Suspensi',
        'engine' => 'Komponen Mesin & Pengapian',
        'ac_cooling' => 'AC & Pendingin',
        'body_accessories' => 'Wiper, Lampu & Aksesoris',
        'other' => 'Lainnya',
    ];

    public const UNITS = [
        'Pcs',
        'Botol',
        'Liter',
        'Set',
        'Galon',
        'Unit',
    ];

    protected $fillable = [
        'part_number',
        'name',
        'category',
        'unit',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'min_stock',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function procurementItems(): HasMany
    {
        return $this->hasMany(SparePartProcurementItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(SparePartStockMovement::class)->orderByDesc('id');
    }

    public function serviceOrderItems(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->min_stock) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Stok Habis',
            'low_stock' => 'Stok Menipis',
            default => 'Tersedia',
        };
    }

    public function getStockBadgeClassAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'bg-rose-50 text-rose-700 border-rose-200',
            'low_stock' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        };
    }

    public function getFormattedPurchasePriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->purchase_price, 0, ',', '.');
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->selling_price, 0, ',', '.');
    }
}
