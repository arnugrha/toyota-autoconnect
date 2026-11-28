<?php

namespace App\Models;

use Database\Factories\VehicleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'category_id',
        'variant',
        'description',
        'price',
        'transmission',
        'fuel_type',
        'engine_capacity',
        'passenger_capacity',
        'dimensions',
        'status',
        'publication_status',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'price' => 'integer',
            'passenger_capacity' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'category_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(VehicleInventory::class);
    }

    public function creditSimulations(): HasMany
    {
        return $this->hasMany(CreditSimulation::class);
    }

    public function leasingApplications(): HasMany
    {
        return $this->hasMany(LeasingApplication::class);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->relationLoaded('inventories')) {
            return (int) $this->inventories->sum('stock_quantity');
        }

        return (int) $this->inventories()->sum('stock_quantity');
    }

    public function getTotalAvailableStockAttribute(): int
    {
        if ($this->relationLoaded('inventories')) {
            return (int) $this->inventories->sum(fn ($inv) => $inv->available_stock);
        }

        return (int) $this->inventories()->get()->sum(fn ($inv) => $inv->available_stock);
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(VehicleImage::class)
            ->ofMany(['is_primary' => 'max', 'sort_order' => 'min', 'id' => 'min'], function (Builder $query) {
                // Return best primary or first image
            });
    }

    /**
     * Scope only published vehicles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publication_status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope featured vehicles.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope available vehicles.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    /**
     * Get the resolved primary image object (with fallback to first image in relation).
     */
    public function getResolvedPrimaryImageAttribute(): ?VehicleImage
    {
        if ($this->relationLoaded('images')) {
            return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        }

        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    /**
     * Get primary image URL or fallback placeholder.
     */
    protected function primaryImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $img = $this->resolved_primary_image;
                if ($img && $img->url) {
                    return $img->url;
                }

                if ($this->slug && file_exists(public_path("images/vehicles/{$this->slug}-exterior.jpg"))) {
                    return asset("images/vehicles/{$this->slug}-exterior.jpg");
                }

                return asset('images/placeholder-car.jpg');
            }
        );
    }

    /**
     * Formatted price in IDR or informative label if null.
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->price === null) {
                    return 'Harga Belum Tersedia';
                }

                return 'Rp '.number_format($this->price, 0, ',', '.');
            }
        );
    }

    /**
     * Human readable status badge config.
     */
    protected function statusBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'available' => [
                        'label' => 'Tersedia',
                        'class' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
                        'dot' => 'bg-emerald-500',
                    ],
                    'incoming' => [
                        'label' => 'Segera Hadir',
                        'class' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
                        'dot' => 'bg-amber-500',
                    ],
                    'reserved' => [
                        'label' => 'Terpesan',
                        'class' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-400/20',
                        'dot' => 'bg-slate-400',
                    ],
                    'sold' => [
                        'label' => 'Terjual',
                        'class' => 'bg-zinc-100 text-zinc-500 ring-1 ring-zinc-400/20',
                        'dot' => 'bg-zinc-400',
                    ],
                    default => [
                        'label' => ucfirst($this->status),
                        'class' => 'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20',
                        'dot' => 'bg-gray-400',
                    ],
                };
            }
        );
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function testDriveBookings(): HasMany
    {
        return $this->hasMany(TestDriveBooking::class);
    }

    public function salesOpportunities(): HasMany
    {
        return $this->hasMany(SalesOpportunity::class);
    }

    public function salesOffers(): HasMany
    {
        return $this->hasMany(SalesOffer::class);
    }

    public function vehicleBookings(): HasMany
    {
        return $this->hasMany(VehicleBooking::class);
    }
}
