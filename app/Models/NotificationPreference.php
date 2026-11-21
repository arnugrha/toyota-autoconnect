<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'in_app',
        'email',
        'whatsapp',
        'service_reminders',
        'transactional',
        'promotional',
        'whatsapp_consent',
        'whatsapp_consent_at',
    ];

    protected function casts(): array
    {
        return [
            'in_app' => 'boolean',
            'email' => 'boolean',
            'whatsapp' => 'boolean',
            'service_reminders' => 'boolean',
            'transactional' => 'boolean',
            'promotional' => 'boolean',
            'whatsapp_consent' => 'boolean',
            'whatsapp_consent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

