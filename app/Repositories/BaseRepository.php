<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * Create a new repository instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Get paginated records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find a record by ID.
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find a record by ID or fail.
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Find by a specific column.
     */
    public function findBy(string $column, $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($column, $value)->first($columns);
    }

    /**
     * Get all records matching a condition.
     */
    public function findAllBy(string $column, $value, array $columns = ['*']): Collection
    {
        return $this->model->where($column, $value)->get($columns);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int $id, array $data): bool
    {
        $model = $this->findOrFail($id);
        return $model->update($data);
    }

    /**
     * Delete a record.
     */
    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Get query builder instance.
     */
    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Apply department scoping if available.
     */
    public function departmentScoped(): Builder
    {
        $query = $this->query();

        if (method_exists($this->model, 'scopeDepartmentScoped')) {
            return $query->departmentScoped();
        }

        return $query;
    }

    /**
     * Count all records.
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if record exists.
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get first record.
     */
    public function first(array $columns = ['*']): ?Model
    {
        return $this->model->first($columns);
    }

    /**
     * Get latest records.
     */
    public function latest(int $limit = 10, array $columns = ['*']): Collection
    {
        return $this->model->latest()->limit($limit)->get($columns);
    }

    /**
     * Search records.
     * Override in child repositories for specific search logic.
     */
    public function search(string $term, array $columns = ['*']): Collection
    {
        // Default implementation - override in child classes
        return $this->model->where(function ($query) use ($term) {
            // This should be overridden with specific search logic
        })->get($columns);
    }

    /**
     * Bulk insert records.
     */
    public function bulkInsert(array $data): bool
    {
        return $this->model->insert($data);
    }

    /**
     * Bulk update records.
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    /**
     * Bulk delete records.
     */
    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Get records with relations.
     */
    public function with(array $relations): Builder
    {
        return $this->query()->with($relations);
    }

    /**
     * Order by column.
     */
    public function orderBy(string $column, string $direction = 'asc'): Builder
    {
        return $this->query()->orderBy($column, $direction);
    }

    /**
     * Apply filters to query.
     */
    public function filter(array $filters): Builder
    {
        $query = $this->query();

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Get fresh model instance.
     */
    public function fresh(): Model
    {
        return $this->model->newInstance();
    }
}
