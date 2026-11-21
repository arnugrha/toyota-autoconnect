<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeasingApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'vehicle_id',
        'credit_simulation_id',
        'leasing_provider_id',
        'sales_opportunity_id',
        'provider_reference',
        'status',
        'vehicle_price',
        'down_payment',
        'tenor_months',
        'monthly_installment',
        'applicant_data',
        'notes',
        'revision_notes',
        'submitted_at',
        'response_at',
    ];

    protected function casts(): array
    {
        return [
            'vehicle_price' => 'integer',
            'down_payment' => 'integer',
            'tenor_months' => 'integer',
            'monthly_installment' => 'integer',
            'applicant_data' => 'array',
            'submitted_at' => 'datetime',
            'response_at' => 'datetime',
        ];
    }

    public static function generateCode(): string
    {
        $prefix = 'LAP-'.now()->format('Ym');
        $last = static::where('code', 'like', "{$prefix}-%")
            ->orderByDesc('id')
            ->first();

        if ($last && preg_match('/(\d+)$/', $last->code, $matches)) {
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('%s-%04d', $prefix, $nextSeq);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creditSimulation(): BelongsTo
    {
        return $this->belongsTo(CreditSimulation::class);
    }

    public function leasingProvider(): BelongsTo
    {
        return $this->belongsTo(LeasingProvider::class);
    }

    public function salesOpportunity(): BelongsTo
    {
        return $this->belongsTo(SalesOpportunity::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LeasingApplicationLog::class)->orderByDesc('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft Pengajuan',
            'submitted' => 'Terkirim ke Leasing',
            'processing' => 'Sedang Diproses Analis',
            'approved' => 'Pembiayaan Disetujui',
            'rejected' => 'Pengajuan Ditolak',
            'cancelled' => 'Pengajuan Dibatalkan',
            'need_revision' => 'Memerlukan Revisi Dokumen',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
            'submitted' => 'bg-blue-50 text-blue-700 border-blue-200',
            'processing' => 'bg-amber-50 text-amber-700 border-amber-200',
            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
            'need_revision' => 'bg-purple-50 text-purple-700 border-purple-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }

    public function getFormattedVehiclePriceAttribute(): string
    {
        return 'Rp ' . number_format($this->vehicle_price, 0, ',', '.');
    }

    public function getFormattedDownPaymentAttribute(): string
    {
        return 'Rp ' . number_format($this->down_payment, 0, ',', '.');
    }

    public function getFormattedMonthlyInstallmentAttribute(): string
    {
        return 'Rp ' . number_format($this->monthly_installment, 0, ',', '.');
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'submitted', 'need_revision'], true);
    }
}

