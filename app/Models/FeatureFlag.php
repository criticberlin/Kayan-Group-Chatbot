<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'description',
        'is_enabled',
        'config',
        'enabled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
        'enabled_at' => 'datetime',
    ];

    /**
     * Scope to enabled features.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to disabled features.
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Enable this feature.
     */
    public function enable(): void
    {
        $this->update([
            'is_enabled' => true,
            'enabled_at' => now(),
        ]);
    }

    /**
     * Disable this feature.
     */
    public function disable(): void
    {
        $this->update([
            'is_enabled' => false,
            'enabled_at' => null,
        ]);
    }

    /**
     * Toggle feature state.
     */
    public function toggle(): void
    {
        if ($this->is_enabled) {
            $this->disable();
        } else {
            $this->enable();
        }
    }

    /**
     * Update feature configuration.
     */
    public function updateConfig(array $config): void
    {
        $this->update(['config' => $config]);
    }

    /**
     * Get configuration value.
     */
    public function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Check if feature is enabled globally.
     */
    public static function isEnabled(string $key): bool
    {
        return static::where('key', $key)->where('is_enabled', true)->exists();
    }

    /**
     * Get feature configuration globally.
     */
    public static function getConfig(string $key, string $configKey = null, $default = null)
    {
        $feature = static::where('key', $key)->first();

        if (!$feature || !$feature->is_enabled) {
            return $default;
        }

        if ($configKey === null) {
            return $feature->config ?? $default;
        }

        return $feature->config[$configKey] ?? $default;
    }
}
