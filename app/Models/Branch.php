<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'email',
        'is_active',
        'opening_time',
        'closing_time',
        'slot_duration_minutes',
        'slot_capacity',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'slot_duration_minutes' => 'integer',
            'slot_capacity' => 'integer',
        ];
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(VehicleInventory::class);
    }

    public function testDriveBookings(): HasMany
    {
        return $this->hasMany(TestDriveBooking::class);
    }

    public function salesOpportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }

    public function vehicleBookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }

    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    public function serviceBookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Dapatkan daftar slot waktu operasional untuk cabang ini.
     *
     * @return array<int, string>
     */
    public function getTimeSlots(): array
    {
        $open = Carbon::createFromFormat('H:i', $this->opening_time ?: '08:30');
        $close = Carbon::createFromFormat('H:i', $this->closing_time ?: '17:00');
        $duration = $this->slot_duration_minutes ?: 60;

        $slots = [];
        $current = $open->copy();

        while ($current->copy()->addMinutes($duration)->lte($close)) {
            $slots[] = $current->format('H:i');
            $current->addMinutes($duration);
        }

        return $slots;
    }

    /**
     * Hitung jumlah booking aktif pada slot tanggal & jam tertentu.
     */
    public function getActiveBookingsCountForSlot(CarbonInterface|string $scheduledAt, ?int $excludeBookingId = null): int
    {
        $dt = $scheduledAt instanceof CarbonInterface ? $scheduledAt : Carbon::parse($scheduledAt);

        $query = $this->testDriveBookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', $dt->format('Y-m-d H:i:s'));

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count();
    }

    /**
     * Periksa apakah slot masih tersedia sesuai kapasitas cabang.
     */
    public function isSlotAvailable(CarbonInterface|string $scheduledAt, ?int $excludeBookingId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $capacity = $this->slot_capacity ?: 2;
        $activeBookings = $this->getActiveBookingsCountForSlot($scheduledAt, $excludeBookingId);

        return $activeBookings < $capacity;
    }
}
