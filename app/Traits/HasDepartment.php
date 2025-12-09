<?php

namespace App\Traits;

trait HasDepartment
{
    /**
     * Boot the HasDepartment trait.
     */
    protected static function bootHasDepartment(): void
    {
        static::creating(function ($model) {
            // Auto-assign department_id from authenticated user if not set
            if (!$model->department_id && auth()->check() && auth()->user()->department_id) {
                $model->department_id = auth()->user()->department_id;
            }

            // Auto-assign created_by if model has this field
            if (in_array('created_by', $model->getFillable()) && !$model->created_by && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            // Auto-assign updated_by if model has this field
            if (in_array('updated_by', $model->getFillable()) && auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Scope query to department-scoped records.
     * 
     * Admins can see all records.
     * Department heads can see their department + sub-departments.
     * Regular users can only see their own department.
     */
    public function scopeDepartmentScoped($query)
    {
        if (!auth()->check()) {
            return $query->whereNull('department_id');
        }

        $user = auth()->user();

        // Super admin sees everything
        if ($user->hasRole('admin') || $user->hasPermission('browse_all_departments')) {
            return $query;
        }

        // Department head sees own department + children
        if ($user->is_department_head && $user->department) {
            $departmentIds = $this->getDepartmentWithDescendants($user->department_id);
            return $query->whereIn('department_id', $departmentIds);
        }

        // Regular user sees only their department
        return $query->where('department_id', $user->department_id);
    }

    /**
     * Get department IDs including all descendants.
     */
    protected function getDepartmentWithDescendants(int $departmentId): array
    {
        $department = \App\Models\Department::with('descendants')->find($departmentId);

        if (!$department) {
            return [$departmentId];
        }

        $ids = [$department->id];

        foreach ($department->descendants as $child) {
            $ids[] = $child->id;
            if ($child->descendants) {
                $ids = array_merge($ids, $this->getDescendantIds($child->descendants));
            }
        }

        return array_unique($ids);
    }

    /**
     * Recursively get descendant IDs.
     */
    protected function getDescendantIds($descendants): array
    {
        $ids = [];

        foreach ($descendants as $descendant) {
            $ids[] = $descendant->id;
            if ($descendant->descendants) {
                $ids = array_merge($ids, $this->getDescendantIds($descendant->descendants));
            }
        }

        return $ids;
    }

    /**
     * Check if user can access this record based on department.
     */
    public function canBeAccessedByUser($user): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can access everything
        if ($user->hasRole('admin') || $user->hasPermission('browse_all_departments')) {
            return true;
        }

        // No department restriction
        if (!$this->department_id) {
            return true;
        }

        // Same department
        if ($this->department_id === $user->department_id) {
            return true;
        }

        // Department head can access sub-departments
        if ($user->is_department_head) {
            $allowedDepartments = $this->getDepartmentWithDescendants($user->department_id);
            return in_array($this->department_id, $allowedDepartments);
        }

        return false;
    }
}
