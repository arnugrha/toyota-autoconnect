<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestDriveBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'vehicle_id',
        'branch_id',
        'lead_id',
        'assigned_to',
        'scheduled_at',
        'duration_minutes',
        'status',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_note',
        'staff_note',
        'rejection_reason',
        'cancellation_reason',
        'confirmed_at',
        'confirmed_by',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'duration_minutes' => 'integer',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'TDB-'.now()->format('Ym');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function confirmedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Jadwal Dikonfirmasi',
            'completed' => 'Test Drive Selesai',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Pengajuan Ditolak',
            'no_show' => 'Pelanggan Tidak Hadir',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'cancelled' => 'bg-slate-100 text-slate-700 border-slate-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'no_show' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Pelanggan dapat membatalkan jika status masih pending atau confirmed dan jadwal belum lewat.
     */
    public function getCanBeCancelledByCustomerAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->scheduled_at->isFuture();
    }
}

