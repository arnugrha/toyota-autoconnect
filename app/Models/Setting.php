<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_PREFIX = 'toyota_setting_';
    public const CACHE_GROUP_PREFIX = 'toyota_settings_group_';
    public const CACHE_TTL_SECONDS = 86400; // 24 hours

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Scope for a specific group of settings.
     */
    public function scopeGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope for publicly accessible settings.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Cast the raw value according to setting type.
     */
    public function getCastedValue(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'decimal', 'float' => (float) $this->value,
            'json' => json_decode($this->value, true) ?? [],
            default => (string) $this->value,
        };
    }

    /**
     * Get a setting value by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL_SECONDS, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if ($setting === null) {
                return $default;
            }

            return $setting->getCastedValue();
        });
    }

    /**
     * Set a setting value by key and invalidate corresponding cache.
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null, ?string $description = null, bool $isPublic = false): self
    {
        $setting = static::firstOrNew(['key' => $key]);

        if ($group !== null) {
            $setting->group = $group;
        }

        if ($type !== null) {
            $setting->type = $type;
        } elseif (! $setting->exists) {
            $setting->type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : (is_array($value) ? 'json' : 'string'));
        }

        if ($description !== null) {
            $setting->description = $description;
        }

        $setting->is_public = $isPublic;

        // Serialize value based on type
        if (is_bool($value)) {
            $setting->value = $value ? '1' : '0';
        } elseif (is_array($value)) {
            $setting->value = json_encode($value);
        } else {
            $setting->value = $value !== null ? (string) $value : null;
        }

        $setting->save();

        // Invalidate specific key and group caches
        Cache::forget(self::CACHE_PREFIX . $key);
        if ($setting->group) {
            Cache::forget(self::CACHE_GROUP_PREFIX . $setting->group);
        }

        return $setting;
    }

    /**
     * Retrieve all settings in a specific group as an associative array.
     */
    public static function getByGroup(string $group): array
    {
        return Cache::remember(self::CACHE_GROUP_PREFIX . $group, self::CACHE_TTL_SECONDS, function () use ($group) {
            $records = static::where('group', $group)->get();
            $result = [];

            foreach ($records as $item) {
                $result[$item->key] = $item->getCastedValue();
            }

            return $result;
        });
    }

    /**
     * Invalidate all setting caches.
     */
    public static function clearAllCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget(self::CACHE_PREFIX . $setting->key);
            if ($setting->group) {
                Cache::forget(self::CACHE_GROUP_PREFIX . $setting->group);
            }
        }
    }
}

