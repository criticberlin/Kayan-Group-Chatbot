<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'message',
        'response',
        'context_used',
        'model',
        'response_time_ms',
        'was_successful',
        'error_message',
    ];

    protected $casts = [
        'context_used' => 'array',
        'was_successful' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)->orderBy('created_at');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('was_successful', true);
    }
}
