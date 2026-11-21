<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'user_id',
        'branch_id',
        'vehicle_id',
        'vehicle_name',
        'license_plate',
        'current_mileage',
        'service_type_id',
        'booking_date',
        'booking_time',
        'customer_notes',
        'status',
        'reschedule_reason',
        'rejection_reason',
        'cancellation_reason',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'current_mileage' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'SRV-' . date('Ym') . '-';
        $latest = static::where('booking_code', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $number = 1;
        if ($latest) {
            $parts = explode('-', $latest->booking_code);
            $number = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function serviceOrder(): HasOne
    {
        return $this->hasOne(ServiceOrder::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Terkonfirmasi',
            'rescheduled' => 'Jadwal Diubah',
            'cancelled' => 'Dibatalkan Pelanggan',
            'rejected' => 'Ditolak Dealer',
            'completed' => 'Servis Selesai',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
            'rescheduled' => 'bg-purple-50 text-purple-700 border-purple-200',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-300',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Check if customer is still allowed to cancel.
     */
    public function canBeCancelled(): bool
    {
        if (in_array($this->status, ['cancelled', 'rejected', 'completed'])) {
            return false;
        }

        // Cannot cancel if service is today or in the past
        $scheduledAt = Carbon::parse($this->booking_date->format('Y-m-d') . ' ' . $this->booking_time);
        return $scheduledAt->isFuture() && $scheduledAt->diffInHours(now()) >= 4;
    }
}

