<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'vehicle_inventory_id',
        'user_id',
        'movement_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
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

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(VehicleInventory::class, 'vehicle_inventory_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'adjustment_in' => 'Penambahan Stok Manual',
            'adjustment_out' => 'Pengurangan Stok Manual',
            'initial_stock' => 'Stok Awal Sistem',
            'reservation' => 'Alokasi / Reservasi Pesanan',
            'release' => 'Pelepasan Reservasi',
            default => ucfirst(str_replace('_', ' ', $this->movement_type)),
        };
    }
}
