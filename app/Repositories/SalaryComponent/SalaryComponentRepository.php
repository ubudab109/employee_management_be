<?php

namespace App\Repositories\SalaryComponent;

use App\Models\SalaryComponent;

class SalaryComponentRepository implements SalaryComponentInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(SalaryComponent $model)
    {
      $this->model = $model;
    }

    /**
     * LIST SALARY COMPONENT
     * @param string $keyword
     * @param string $type
     * @return array
     */
    public function listSalaryComponent($keyword, $type)
    {
      return $this->model
      ->when($keyword !== null || $keyword !== '', function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%'. $keyword .'%');
      })
      ->when($type !== '', function ($query) use ($type) {
        $query->where('type', $type);
      })
      ->orderBy('name', 'asc')->get();
    }

    /**
     * CREATE SALARY COMPONENT
     * @param array $data
     * @return object
     */
    public function createSalaryComponent(array $data)
    {
      return $this->model->create($data);
    }

    /**
     * UPDATE SALARY COMPONENT
     * @param array $data
     * @param integer $id
     * @return bool
     */
    public function updateSalaryComponent(array $data, $id)
    {
      return $this->model->find($id)->update($data);
    }
}