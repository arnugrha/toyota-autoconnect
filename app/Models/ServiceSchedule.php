<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'day_of_week',
        'opening_time',
        'closing_time',
        'slot_duration_minutes',
        'max_slot_capacity',
        'max_daily_capacity',
        'is_operational',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration_minutes' => 'integer',
            'max_slot_capacity' => 'integer',
            'max_daily_capacity' => 'integer',
            'is_operational' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getDayNameAttribute(): string
    {
        return match ($this->day_of_week) {
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Tidak Diketahui',
        };
    }

    /**
     * Generate available time slots based on opening and closing times.
     *
     * @return array<int, string>
     */
    public function getTimeSlots(): array
    {
        if (! $this->is_operational) {
            return [];
        }

        $slots = [];
        $start = Carbon::createFromTimeString($this->opening_time);
        $end = Carbon::createFromTimeString($this->closing_time);
        $duration = $this->slot_duration_minutes ?: 60;

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $slots[] = $start->format('H:i');
            $start->addMinutes($duration);
        }

        return $slots;
    }
}

