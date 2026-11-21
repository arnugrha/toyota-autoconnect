<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_id',
        'vehicle_name',
        'license_plate',
        'last_service_date',
        'last_mileage',
        'next_service_date',
        'next_service_mileage',
        'recommended_service',
        'status',
        'reminder_sent_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_service_date' => 'date',
            'next_service_date' => 'date',
            'last_mileage' => 'integer',
            'next_service_mileage' => 'integer',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Jadwal',
            'sent' => 'Pengingat Terkirim',
            'booked' => 'Telah Dibooking',
            'dismissed' => 'Diabaikan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
            'sent' => 'bg-blue-50 text-blue-700 border-blue-200',
            'booked' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dismissed' => 'bg-slate-100 text-slate-600 border-slate-300',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}

