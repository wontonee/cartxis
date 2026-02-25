<?php

namespace Cartxis\Shop\Repositories;

use Cartxis\Shop\Contracts\ShopRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class ShopRepository implements ShopRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * ShopRepository constructor.
     */
    public function __construct()
    {
        $this->makeModel();
    }

    /**
     * Get all records.
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = ['*'])
    {
        return $this->model->get($columns);
    }

    /**
     * Find a record by id.
     *
     * @param int $id
     * @param array $columns
     * @return Model|null
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find a record by field and value.
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return Model|null
     */
    public function findByField($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * Find records where field value is in given array.
     *
     * @param string $field
     * @param array $values
     * @param array $columns
     * @return Collection
     */
    public function findWhereIn($field, array $values, $columns = ['*'])
    {
        return $this->model->whereIn($field, $values)->get($columns);
    }

    /**
     * Create a new record.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update(array $data, $id)
    {
        $record = $this->find($id);
        
        if ($record) {
            $record->update($data);
            return $record->fresh();
        }

        return null;
    }

    /**
     * Delete a record.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $record = $this->find($id);
        
        if ($record) {
            return $record->delete();
        }

        return false;
    }

    /**
     * Get paginated records.
     *
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Apply query scopes.
     *
     * @param \Closure $callback
     * @return $this
     */
    public function scopeQuery(\Closure $callback)
    {
        $this->model = $callback($this->model);
        
        return $this;
    }

    /**
     * Reset model to original state.
     *
     * @return $this
     */
    public function resetModel()
    {
        $this->makeModel();
        
        return $this;
    }

    /**
     * Get a new instance of the model.
     *
     * @return Model
     */
    abstract public function model();

    /**
     * Make model instance.
     *
     * @return Model
     */
    public function makeModel()
    {
        $model = app($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }
}
