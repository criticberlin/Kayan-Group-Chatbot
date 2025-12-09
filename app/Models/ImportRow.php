<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'import_id',
        'row_number',
        'raw_data',
        'parsed_data',
        'status',
        'target_table',
        'target_id',
        'validation_errors',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'row_number' => 'integer',
        'raw_data' => 'array',
        'parsed_data' => 'array',
        'target_id' => 'integer',
        'validation_errors' => 'array',
    ];

    /**
     * Get the parent import.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Scope to pending rows.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to validated rows.
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    /**
     * Scope to imported rows.
     */
    public function scopeImported($query)
    {
        return $query->where('status', 'imported');
    }

    /**
     * Scope to failed rows.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Check if row has errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->validation_errors);
    }

    /**
     * Mark row as validated.
     */
    public function markAsValidated(?array $parsedData = null): void
    {
        $this->update([
            'status' => 'validated',
            'parsed_data' => $parsedData ?? $this->raw_data,
        ]);
    }

    /**
     * Mark row as imported.
     */
    public function markAsImported(string $targetTable, int $targetId): void
    {
        $this->update([
            'status' => 'imported',
            'target_table' => $targetTable,
            'target_id' => $targetId,
        ]);
    }

    /**
     * Mark row as failed.
     */
    public function markAsFailed(array $errors): void
    {
        $this->update([
            'status' => 'failed',
            'validation_errors' => $errors,
        ]);
    }
}
