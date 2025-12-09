<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatContext extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'source_table',
        'source_id',
        'context_text',
        'keywords',
        'relevance_score',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'source_id' => 'integer',
        'relevance_score' => 'integer',
    ];

    /**
     * Get the department this context belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the source model dynamically.
     */
    public function source()
    {
        $modelClass = $this->getModelClassName();

        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        return $modelClass::find($this->source_id);
    }

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['context_text', 'keywords'], $term);
    }

    /**
     * Scope by source table.
     */
    public function scopeBySource($query, string $sourceTable)
    {
        return $query->where('source_table', $sourceTable);
    }

    /**
     * Scope to high relevance contexts.
     */
    public function scopeHighRelevance($query, int $minScore = 50)
    {
        return $query->where('relevance_score', '>=', $minScore);
    }

    /**
     * Get model class name from source table.
     */
    protected function getModelClassName(): ?string
    {
        $mapping = [
            'products' => Product::class,
            'faq_records' => FaqRecord::class,
            'policy_documents' => PolicyDocument::class,
            'hr_records' => HrRecord::class,
            'documents' => Document::class,
        ];

        return $mapping[$this->source_table] ?? null;
    }

    /**
     * Get keywords as array.
     */
    public function getKeywordsArrayAttribute(): array
    {
        if (empty($this->keywords)) {
            return [];
        }

        return array_map('trim', explode(',', $this->keywords));
    }

    /**
     * Update relevance score.
     */
    public function updateRelevanceScore(int $score): void
    {
        $this->update(['relevance_score' => $score]);
    }
}
