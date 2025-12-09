<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            if (!auth()->check()) {
                return;
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'department_id' => auth()->user()->department_id ?? null,
                'action' => 'created',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => null,
                'new_values' => $model->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::updated(function ($model) {
            if (!auth()->check()) {
                return;
            }

            // Only log if there are actual changes
            if (!$model->wasChanged()) {
                return;
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'department_id' => auth()->user()->department_id ?? null,
                'action' => 'updated',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => array_intersect_key($model->getOriginal(), $model->getDirty()),
                'new_values' => $model->getDirty(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        static::deleted(function ($model) {
            if (!auth()->check()) {
                return;
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'department_id' => auth()->user()->department_id ?? null,
                'action' => 'deleted',
                'auditable_type' => get_class($model),
                'auditable_id' => $model->id,
                'old_values' => $model->toArray(),
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // Log soft deletes if model uses SoftDeletes trait
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(static::class))) {
            static::restored(function ($model) {
                if (!auth()->check()) {
                    return;
                }

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'department_id' => auth()->user()->department_id ?? null,
                    'action' => 'restored',
                    'auditable_type' => get_class($model),
                    'auditable_id' => $model->id,
                    'old_values' => null,
                    'new_values' => $model->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            });
        }
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get the latest audit log.
     */
    public function latestAudit()
    {
        return $this->auditLogs()->latest()->first();
    }
}
