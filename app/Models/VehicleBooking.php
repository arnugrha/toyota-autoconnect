<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'sales_opportunity_id',
        'sales_offer_id',
        'customer_id',
        'vehicle_id',
        'branch_id',
        'inventory_id',
        'vehicle_price',
        'discount_amount',
        'booking_fee',
        'booking_date',
        'expires_at',
        'status',
        'payment_status',
        'cancellation_reason',
        'notes',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_price' => 'integer',
            'discount_amount' => 'integer',
            'booking_fee' => 'integer',
            'booking_date' => 'date',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'VBK-'.now()->format('Ym');
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

    public function salesOffer(): BelongsTo
    {
        return $this->belongsTo(SalesOffer::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(VehicleInventory::class, 'inventory_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('id');
    }

    public function latestPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function getNetPriceAttribute(): int
    {
        return max(0, $this->vehicle_price - $this->discount_amount);
    }

    public function getNetUnitPriceAttribute(): int
    {
        return $this->net_price;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'confirmed' => 'Booking Dikonfirmasi',
            'cancelled' => 'Booking Dibatalkan',
            'expired' => 'Kedaluwarsa',
            'completed' => 'Selesai (Unit Diserahkan)',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
            'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
            'completed' => 'bg-blue-50 text-blue-700 border-blue-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Tanda Jadi Lunas (Midtrans)',
            'unpaid' => 'Belum Terverifikasi / Belum Ada Tanda Jadi',
            'verified' => 'Tanda Jadi Terverifikasi (Internal)',
            'refunded' => 'Tanda Jadi Dikembalikan (Refund)',
            default => ucfirst($this->payment_status),
        };
    }
}

