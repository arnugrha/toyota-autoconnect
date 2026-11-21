<?php

namespace App\Models;

use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    protected $fillable = [
        'lead_number',
        'customer_id',
        'inquiry_id',
        'vehicle_id',
        'assigned_to',
        'status',
        'source',
        'notes',
        'next_follow_up_at',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'next_follow_up_at' => 'datetime',
            'last_activity_at' => 'datetime',
        ];
    }

    public static function generateLeadNumber(): string
    {
        $prefix = 'LD-'.now()->format('Ym');
        $lastLead = static::where('lead_number', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastLead && preg_match('/(\d+)$/', $lastLead->lead_number, $matches)) {
            $nextSequence = intval($matches[1]) + 1;
        } else {
            $nextSequence = 1;
        }

        return sprintf('%s-%04d', $prefix, $nextSequence);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedSales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function testDriveBookings(): HasMany
    {
        return $this->hasMany(TestDriveBooking::class);
    }

    public function salesOpportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }

    public function getCustomerNameAttribute(): string
    {
        return $this->customer?->name ?? 'Prospek';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new' => 'Prospek Baru',
            'contacted' => 'Sudah Dihubungi',
            'interested' => 'Berminat / Hot Lead',
            'test_drive' => 'Jadwal Test Drive',
            'negotiation' => 'Negosiasi Penawaran',
            'booking' => 'SPK / Booking Fee',
            'deal' => 'Deal (Penjualan)',
            'lost' => 'Batal / Lost',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-blue-50 text-blue-700 border-blue-200',
            'contacted' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'interested' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'test_drive' => 'bg-purple-50 text-purple-700 border-purple-200',
            'negotiation' => 'bg-amber-50 text-amber-700 border-amber-200',
            'booking' => 'bg-orange-50 text-orange-700 border-orange-200',
            'deal' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'lost' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->next_follow_up_at) {
            return false;
        }

        return $this->next_follow_up_at->isPast() && ! in_array($this->status, ['deal', 'lost']);
    }
}
