<?php

namespace App\Models;

use Database\Factories\LeadActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    /** @use HasFactory<LeadActivityFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'user_id',
        'activity_type',
        'description',
        'previous_status',
        'new_status',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivityTypeLabelAttribute(): string
    {
        return match ($this->activity_type) {
            'created' => 'Pembuatan Prospek',
            'status_changed' => 'Perubahan Status',
            'assigned' => 'Penugasan Sales',
            'follow_up_note' => 'Catatan Follow-up',
            'contacted' => 'Menghubungi Customer',
            'inquiry_responded' => 'Jawaban Inquiry',
            default => ucfirst(str_replace('_', ' ', $this->activity_type)),
        };
    }
}
