<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'vehicle_booking_id',
        'user_id',
        'gateway',
        'gateway_transaction_id',
        'order_id',
        'amount',
        'payment_method',
        'status',
        'snap_token',
        'snap_redirect_url',
        'gateway_response',
        'paid_at',
        'expired_at',
        'refund_amount',
        'refund_reason',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'refund_amount' => 'integer',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'PAY-'.now()->format('Ym');
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

    public static function generateOrderId(VehicleBooking $booking): string
    {
        return sprintf('BOOK-%s-%s', $booking->id, time());
    }

    public function vehicleBooking(): BelongsTo
    {
        return $this->belongsTo(VehicleBooking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class)->orderByDesc('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Pembayaran Berhasil',
            'failed' => 'Pembayaran Gagal',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan (Refund)',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
            'expired' => 'bg-slate-100 text-slate-700 border-slate-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            'refunded' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

