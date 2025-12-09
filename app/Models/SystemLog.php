<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'level',
        'category',
        'message',
        'context',
        'exception',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Scope by level.
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to errors.
     */
    public function scopeErrors($query)
    {
        return $query->whereIn('level', ['error', 'critical']);
    }

    /**
     * Scope to recent logs.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Log an info message.
     */
    public static function info(string $category, string $message, array $context = []): void
    {
        static::create([
            'level' => 'info',
            'category' => $category,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Log a warning message.
     */
    public static function warning(string $category, string $message, array $context = []): void
    {
        static::create([
            'level' => 'warning',
            'category' => $category,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Log an error message.
     */
    public static function error(string $category, string $message, ?\Throwable $exception = null, array $context = []): void
    {
        static::create([
            'level' => 'error',
            'category' => $category,
            'message' => $message,
            'context' => $context,
            'exception' => $exception ? $exception->__toString() : null,
        ]);
    }

    /**
     * Log a critical message.
     */
    public static function critical(string $category, string $message, ?\Throwable $exception = null, array $context = []): void
    {
        static::create([
            'level' => 'critical',
            'category' => $category,
            'message' => $message,
            'context' => $context,
            'exception' => $exception ? $exception->__toString() : null,
        ]);
    }
}
