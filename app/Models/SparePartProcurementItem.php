<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartProcurementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'spare_part_procurement_id',
        'spare_part_id',
        'quantity',
        'unit_cost',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(SparePartProcurement::class, 'spare_part_procurement_id');
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return 'Rp '.number_format((float) $this->unit_cost, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->subtotal, 0, ',', '.');
    }
}
