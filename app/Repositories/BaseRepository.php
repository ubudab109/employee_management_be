<?php

namespace App\Repositories;

use Illuminate\Support\Arr;

abstract class BaseRepository
{
    use RepositoryTrait;

    public function getAll($relations = null, $orders = null)
    {
        $query = $this->model->with($this->extractToArray($relations));

        if (!empty($orders) && is_array($orders)) {
            $query = $query->orderBy($orders);
        }

        return $query->get();
    }

    public function getByConditions(array $conditions, $relations = null, $orders = null)
    {
        $query = $this->model->where($conditions)->with($this->extractToArray($relations));

        if (!empty($orders) && is_array($orders)) {
            $query = $query->orderBy($orders);
        }

        return $query->get();
    }

    public function findOrFailById(int $id, $relations = null)
    {
        return $this->model->with($this->extractToArray($relations))->findOrFail($id);
    }

    public function findOrFailByConditions(array $conditions, $relations = null)
    {
        $instant = $this->model->where($conditions)->with($this->extractToArray($relations))->first();
        abort_if(empty($instant), 404);
        return $instant;
    }

    public function create(array $attributes)
    {
        return $this->model->create(Arr::only($attributes, $this->model->getFillable()));
    }

    public function update(array $attributes, int $id, string $attribute = "id")
    {
        $instance = $this->getFirstByConditions([$attribute => $id]);

        if (empty($instance)) {
            return false;
        }

        return $instance->update(Arr::only($attributes, $this->model->getFillable())) ? $instance : false;
    }

    public function getFirstByConditions(array $conditions, $relations = null)
    {
        return $this->model->where($conditions)->with($this->extractToArray($relations))->first();
    }

    public function updateByConditions(array $attributes, array $conditions)
    {
        $instance = $this->getFirstByConditions($conditions);

        if (empty($instance)) {
            return false;
        }

        return $instance->update(Arr::only($attributes, $this->model->getFillable())) ? $instance : false;
    }

    public function deleteById(int $id)
    {
        $instance = $this->getFirstById($id);

        if (empty($instance)) {
            return false;
        }

        try {
            $instance->delete();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function getFirstById(int $id, $relations = null)
    {
        return $this->model->where('id', $id)->with($this->extractToArray($relations))->first();
    }

    public function deleteByConditions(array $conditions)
    {
        $instance = $this->getFirstByConditions($conditions);

        if (empty($instance)) {
            return false;
        }

        try {
            $instance->delete();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public function toggleStatusById(int $id, string $attribute = 'is_active')
    {
        $instance = $this->getFirstById($id);

        if (empty($instance)) {
            return false;
        }

        $instance->{$attribute} = $instance->{$attribute} ? ACTIVE_STATUS_INACTIVE : ACTIVE_STATUS_ACTIVE;

        if ($instance->update()) {
            return $instance;
        }

        return $instance->update() ? $instance : false;
    }

    public function toggleStatusByConditions(array $conditions, string $attribute = 'is_active')
    {
        $instance = $this->getFirstByConditions($conditions);

        if (empty($instance)) {
            return false;
        }

        $instance->{$attribute} = $instance->{$attribute} ? ACTIVE_STATUS_INACTIVE : ACTIVE_STATUS_ACTIVE;

        return $instance->update() ? $instance : false;
    }

    public function insert(array $attributes)
    {
        return $this->model->insert($attributes);
    }

}