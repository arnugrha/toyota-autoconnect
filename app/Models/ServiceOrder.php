<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'service_booking_id',
        'user_id',
        'branch_id',
        'vehicle_id',
        'vehicle_name',
        'license_plate',
        'current_mileage',
        'service_type_id',
        'service_advisor_id',
        'technician_name',
        'status',
        'customer_complaints',
        'inspection_result',
        'repair_actions',
        'estimated_cost',
        'final_cost',
        'additional_notes',
        'received_at',
        'completed_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'current_mileage' => 'integer',
            'estimated_cost' => 'decimal:2',
            'final_cost' => 'decimal:2',
            'received_at' => 'datetime',
            'completed_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = 'WO-' . date('Ym') . '-';
        $latest = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $number = 1;
        if ($latest) {
            $parts = explode('-', $latest->order_number);
            $number = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class);
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

    public function serviceAdvisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'service_advisor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceOrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ServiceOrderLog::class)->orderByDesc('id');
    }

    public function recordLog(string $action, ?string $oldStatus = null, ?string $newStatus = null, ?string $notes = null, ?int $userId = null): ServiceOrderLog
    {
        return $this->logs()->create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
        ]);
    }

    public function recalculateFinalCost(): void
    {
        $total = $this->items()->sum('subtotal');
        $this->update(['final_cost' => $total]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Terjadwal',
            'confirmed' => 'Dikonfirmasi',
            'vehicle_received' => 'Kendaraan Diterima',
            'inspection' => 'Pemeriksaan / Diagnosa',
            'in_progress' => 'Sedang Dikerjakan',
            'waiting_parts' => 'Menunggu Suku Cadang',
            'completed' => 'Pengerjaan Selesai',
            'delivered' => 'Kendaraan Diserahkan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'bg-slate-50 text-slate-700 border-slate-200',
            'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
            'vehicle_received' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'inspection' => 'bg-amber-50 text-amber-700 border-amber-200',
            'in_progress' => 'bg-sky-50 text-sky-700 border-sky-200',
            'waiting_parts' => 'bg-orange-50 text-orange-700 border-orange-200',
            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'delivered' => 'bg-teal-50 text-teal-700 border-teal-200',
            'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getTotalCostAttribute(): float
    {
        return (float) ($this->final_cost ?? $this->estimated_cost ?? 0);
    }

    public function getFormattedEstimatedCostAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->estimated_cost, 0, ',', '.');
    }

    public function getFormattedFinalCostAttribute(): string
    {
        return $this->final_cost !== null
            ? 'Rp ' . number_format((float) $this->final_cost, 0, ',', '.')
            : '-';
    }
}

