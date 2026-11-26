<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_order_id',
        'item_type',
        'spare_part_id',
        'service_type_id',
        'name',
        'quantity',
        'unit_price',
        'subtotal',
        'approval_status',
        'approved_at',
        'rejected_at',
        'customer_response_note',
        'stock_deducted',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'stock_deducted' => 'boolean',
        ];
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'labor', 'service' => 'Jasa Servis',
            'part' => 'Suku Cadang',
            default => ucfirst($this->item_type),
        };
    }

    public function getApprovalStatusLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'pending_approval' => 'Menunggu Konfirmasi User',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak User',
            default => ucfirst((string) $this->approval_status),
        };
    }

    public function getApprovalBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            'pending_approval' => 'bg-amber-50 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-600 border-slate-200',
        };
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'Rp '.number_format((float) $this->unit_price, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->subtotal, 0, ',', '.');
    }
}
