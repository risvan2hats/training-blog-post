<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseService
{
    protected $model;

    /**
     * Find a model by ID with optional relationships
     */
    public function find($id, array $with = []): ?Model
    {
        return $this->model->with($with)->find($id);
    }

    /**
     * Get all models with optional relationships
     */
    public function all(array $with = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with($with)->get();
    }

    /**
     * Create a new model with transaction handling
     *
     * @throws \Exception
     */
    public function create(array $data): Model
    {
        DB::beginTransaction();

        try {
            $model = $this->model->create($data);
            DB::commit();
            return $model;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update a model with transaction handling
     *
     * @throws \Exception
     */
    public function update($id, array $data): Model
    {
        DB::beginTransaction();

        try {
            $model = $this->findOrFail($id);
            $model->update($data);
            DB::commit();
            return $model->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a model with transaction handling
     *
     * @throws \Exception
     */
    public function delete($id): bool
    {
        DB::beginTransaction();

        try {
            $model = $this->findOrFail($id);
            $result = $model->delete();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15, array $with = []): LengthAwarePaginator
    {
        return $this->model->with($with)->paginate($perPage);
    }

    /**
     * Find or fail by ID
     */
    public function findOrFail($id, array $with = []): Model
    {
        return $this->model->with($with)->findOrFail($id);
    }

    /**
     * Get models with where condition
     */
    public function getWhere(array $conditions, array $with = [], $orderBy = 'created_at', $sort = 'desc'): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with($with)
            ->where($conditions)
            ->orderBy($orderBy, $sort)
            ->get();
    }

    /**
     * Get paginated models with where condition
     */
    public function paginateWhere(array $conditions, int $perPage = 15, array $with = [], $orderBy = 'created_at', $sort = 'desc'): LengthAwarePaginator
    {
        return $this->model->with($with)
            ->where($conditions)
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);
    }
}