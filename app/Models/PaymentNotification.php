<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'order_id',
        'notification_id',
        'notification_payload',
        'processing_status',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_payload' => 'array',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

