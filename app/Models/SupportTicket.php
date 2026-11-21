<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'branch_id',
        'assigned_to',
        'category',
        'priority',
        'status',
        'subject',
        'description',
        'attachment_path',
        'resolved_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = 'TCK-' . date('Ym') . '-';
        $latest = static::where('ticket_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        $number = 1;
        if ($latest) {
            $parts = explode('-', $latest->ticket_number);
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

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->orderBy('id');
    }

    public function customerMessages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class)->where('is_internal', false)->orderBy('id');
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'vehicle_info' => 'Informasi Kendaraan',
            'service_booking' => 'Booking Servis',
            'service_complaint' => 'Keluhan Layanan Bengkel',
            'payment' => 'Pembayaran & Keuangan',
            'warranty' => 'Klaim Garansi',
            'general_inquiry' => 'Pertanyaan Umum',
            default => 'Lainnya',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'Mendesak / Urgent',
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => ucfirst($this->priority),
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-rose-100 text-rose-800 border-rose-300 font-extrabold',
            'high' => 'bg-amber-100 text-amber-800 border-amber-300',
            'medium' => 'bg-blue-50 text-blue-700 border-blue-200',
            'low' => 'bg-slate-100 text-slate-600 border-slate-300',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Tiket Baru (Open)',
            'in_progress' => 'Sedang Ditangani',
            'waiting_customer' => 'Menunggu Balasan Pelanggan',
            'resolved' => 'Selesai (Resolved)',
            'closed' => 'Ditutup (Closed)',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'open' => 'bg-blue-50 text-blue-700 border-blue-200',
            'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
            'waiting_customer' => 'bg-purple-50 text-purple-700 border-purple-200',
            'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'closed' => 'bg-slate-100 text-slate-600 border-slate-300',
            default => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }
}

