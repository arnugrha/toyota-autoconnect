<?php

namespace App\Models;

use Database\Factories\VehicleImageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleImage extends Model
{
    /** @use HasFactory<VehicleImageFactory> */
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the full URL to the image, handling local storage or asset paths.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->image_path)) {
                    return asset('images/placeholder-car.jpg');
                }

                $path = $this->image_path;

                // Always prefer real photographic images (.jpg) over vector illustrations (.svg)
                if (str_ends_with(strtolower($path), '.svg')) {
                    $jpgCandidate = preg_replace('/\.svg$/i', '.jpg', $path);
                    if (file_exists(public_path($jpgCandidate))) {
                        $path = $jpgCandidate;
                    }
                }

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                if (str_starts_with($path, 'images/')) {
                    return asset($path);
                }

                return Storage::url($path);
            }
        );
    }
}
