<?php

namespace App\Repositories\CompanyDivision;

use App\Models\CompanyDivision;

class CompanyDivisionRepository implements CompanyDivisionInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(CompanyDivision $model)
    {
      $this->model = $model;
    }

    /**
     * Get All Without Paginate Division
     * 
     * @param string $keyword
     * @return \App\Models\CompanyDivision
     */
    public function getAllDivision($keyword)
    {
      return $this->model->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('division_name', 'LIKE', '%'.$keyword.'%')->orWhere('division_code', 'LIKE', '%'.$keyword.'%');
      })->get();
    }

    /**
     * Get Data Division With Paginate
     * 
     * @param string $keyword
     * @param int $show
     * @return \App\Model\CompanyDivision
     */
    public function getPaginateDivision($keyword, $show)
    {
      return $this->model->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('division_name', 'LIKE', '%'.$keyword.'%')->orWhere('division_code', 'LIKE', '%'.$keyword.'%');
      })->paginate($show);
    }

    /**
     * Get Detail Division By Id
     * 
     * @param int $id
     * @return \App\Models\CompanyDivision
     */
    public function detailDivision($id)
    {
      return $this->model->findOrFail($id);
    }

    /**
     * Store New Division Data
     * 
     * @param array $data
     * @return \App\Models\CompanyDivision
     */
    public function storeDivision(array $data)
    {
      return $this->model->create($data);
    }

    /**
     * Update Existing Division Data By ID
     * 
     * @param array $data
     * @param int $id
     * @return \App\Models\CompanyDivision
     */
    public function updateDivision(array $data, $id)
    {
      return $this->model->findOrFail($id)->update($data);
    }

    /**
     * Delete Existing Division Data By ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteDivision($id)
    {
      return $this->model->findOrFail($id)->delete();
    }

}