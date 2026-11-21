<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class SalesOpportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'lead_id',
        'user_id',
        'vehicle_id',
        'branch_id',
        'assigned_to',
        'stage',
        'estimated_value',
        'notes',
        'lost_reason',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'integer',
            'closed_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'OPP-'.now()->format('Ym');
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(SalesActivity::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(SalesOffer::class)->orderByDesc('id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class)->orderByDesc('id');
    }

    public function leasingApplications(): HasMany
    {
        return $this->hasMany(LeasingApplication::class)->orderByDesc('id');
    }

    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'prospecting' => 'Prospek Awal',
            'qualification' => 'Kualifikasi Kebutuhan',
            'presentation' => 'Presentasi & Penawaran',
            'test_drive' => 'Uji Kemudi (Test Drive)',
            'negotiation' => 'Negosiasi Harga',
            'booking' => 'Booking Unit (SPK)',
            'closed_won' => 'Penjualan Berhasil (Won)',
            'closed_lost' => 'Batal / Kalah (Lost)',
            default => ucfirst(str_replace('_', ' ', $this->stage)),
        };
    }

    public function getStageBadgeClassAttribute(): string
    {
        return match ($this->stage) {
            'prospecting' => 'bg-slate-100 text-slate-700 border-slate-200',
            'qualification' => 'bg-sky-50 text-sky-700 border-sky-200',
            'presentation' => 'bg-blue-50 text-blue-700 border-blue-200',
            'test_drive' => 'bg-purple-50 text-purple-700 border-purple-200',
            'negotiation' => 'bg-amber-50 text-amber-700 border-amber-200',
            'booking' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'closed_won' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'closed_lost' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    /**
     * Catat aktivitas penjualan pada opportunity ini.
     */
    public function recordActivity(string $type, string $description, ?array $metadata = null, ?int $userId = null): SalesActivity
    {
        return $this->activities()->create([
            'user_id' => $userId ?? Auth::id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}

