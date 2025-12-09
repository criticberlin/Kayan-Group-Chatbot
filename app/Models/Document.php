<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDepartment;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'user_id',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'document_type',
        'category',
        'extracted_text',
        'metadata',
        'version',
        'parent_id',
        'is_latest',
        'access_level',
        'download_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'version' => 'integer',
        'is_latest' => 'boolean',
        'download_count' => 'integer',
    ];

    /**
     * Get the department this document belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who uploaded this document.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent document (for versioning).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    /**
     * Get all versions of this document.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id')->orderBy('version');
    }

    /**
     * Scope to latest versions only.
     */
    public function scopeLatest($query)
    {
        return $query->where('is_latest', true);
    }

    /**
     * Scope by document type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'description', 'extracted_text'], $term);
    }

    /**
     * Increment download count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if document has extracted text.
     */
    public function hasExtractedText(): bool
    {
        return !empty($this->extracted_text);
    }

    /**
     * Check if user can access this document.
     */
    public function canBeAccessedBy(?User $user): bool
    {
        if ($this->access_level === 'public') {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($this->access_level === 'company') {
            return true;
        }

        return $this->department_id === $user->department_id;
    }
}
