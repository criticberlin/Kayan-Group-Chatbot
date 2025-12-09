<?php

namespace App\Traits;

trait DepartmentScoped
{
    /**
     * Boot the DepartmentScoped trait.
     * 
     * This trait automatically applies department scoping to all queries.
     * Unlike HasDepartment which provides a scope method, this one is always applied.
     */
    protected static function bootDepartmentScoped(): void
    {
        static::addGlobalScope('department', function ($builder) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            // Super admin bypasses scope
            if ($user->hasRole('admin') || $user->hasPermission('browse_all_departments')) {
                return;
            }

            // Department head sees own + children
            if ($user->is_department_head && $user->department_id) {
                $departmentIds = static::getDepartmentTree($user->department_id);
                $builder->whereIn($builder->getModel()->getTable() . '.department_id', $departmentIds);
                return;
            }

            // Regular user sees only their department
            if ($user->department_id) {
                $builder->where($builder->getModel()->getTable() . '.department_id', $user->department_id);
            }
        });
    }

    /**
     * Get all department IDs in tree (self + descendants).
     */
    protected static function getDepartmentTree(int $departmentId): array
    {
        static $cache = [];

        if (isset($cache[$departmentId])) {
            return $cache[$departmentId];
        }

        $department = \App\Models\Department::with('descendants')->find($departmentId);

        if (!$department) {
            return [$departmentId];
        }

        $ids = [$department->id];

        foreach ($department->descendants as $child) {
            $ids = array_merge($ids, static::getDepartmentTree($child->id));
        }

        $cache[$departmentId] = array_unique($ids);

        return $cache[$departmentId];
    }

    /**
     * Scope to include records without department.
     */
    public function scopeIncludePublic($query)
    {
        return $query->withoutGlobalScope('department')->where(function ($q) {
            $q->whereNull('department_id');

            if (auth()->check()) {
                $user = auth()->user();

                if ($user->hasRole('admin') || $user->hasPermission('browse_all_departments')) {
                    // Admin sees all
                } elseif ($user->is_department_head && $user->department_id) {
                    $departmentIds = static::getDepartmentTree($user->department_id);
                    $q->orWhereIn('department_id', $departmentIds);
                } elseif ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            }
        });
    }
}
