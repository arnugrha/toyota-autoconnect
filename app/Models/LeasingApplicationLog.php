<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeasingApplicationLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'leasing_application_id',
        'user_id',
        'event_type',
        'description',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function leasingApplication(): BelongsTo
    {
        return $this->belongsTo(LeasingApplication::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

