<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class N8nJob extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'import_id',
        'webhook_url',
        'request_payload',
        'response_payload',
        'status',
        'retry_count',
        'error_message',
        'idempotency_key',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'retry_count' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the import associated with this job.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Scope to pending jobs.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to processing jobs.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope to completed jobs.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to failed jobs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to retryable jobs.
     */
    public function scopeRetryable($query, int $maxRetries = 3)
    {
        return $query->where('status', 'failed')
            ->where('retry_count', '<', $maxRetries);
    }

    /**
     * Check if job is in progress.
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if job is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if job can be retried.
     */
    public function canRetry(int $maxRetries = 3): bool
    {
        return $this->hasFailed() && $this->retry_count < $maxRetries;
    }

    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark job as completed.
     */
    public function markAsCompleted(array $response): void
    {
        $this->update([
            'status' => 'completed',
            'response_payload' => $response,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Increment retry count.
     */
    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }
}
