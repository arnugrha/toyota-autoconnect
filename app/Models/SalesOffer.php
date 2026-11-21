<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sales_opportunity_id',
        'customer_id',
        'vehicle_id',
        'vehicle_price',
        'discount_amount',
        'additional_cost',
        'total_amount',
        'valid_until',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_price' => 'integer',
            'discount_amount' => 'integer',
            'additional_cost' => 'integer',
            'total_amount' => 'integer',
            'valid_until' => 'date',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'OFR-'.now()->format('Ym');
        $last = static::where('code', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/(\d+)$/', $last->code, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('%s-%04d', $prefix, $nextSeq);
    }

    public function salesOpportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === 'sent' && $this->valid_until->isPast()) {
            return 'Kedaluwarsa';
        }

        return match ($this->status) {
            'draft' => 'Draf Penawaran',
            'sent' => 'Terkirim ke Pelanggan',
            'accepted' => 'Disetujui Pelanggan',
            'rejected' => 'Ditolak Pelanggan',
            'expired' => 'Kedaluwarsa',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->status === 'sent' && $this->valid_until->isPast()) {
            return 'bg-rose-50 text-rose-700 border-rose-200';
        }

        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
            'sent' => 'bg-blue-50 text-blue-700 border-blue-200',
            'accepted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'expired' => 'bg-slate-200 text-slate-600 border-slate-300',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}

