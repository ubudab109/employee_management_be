<?php

namespace App\Repositories\CompanyJobStatus;

use App\Models\CompanyJobStatus;

class CompanyJobStatusRepository implements CompanyJobStatusInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(CompanyJobStatus $model)
    {
      $this->model = $model;
    }

    /**
     * Get All Job Status Without Paginate
     * 
     * @param string $keyword
     * @return \App\Models\CompanyJobStatus
    */
    public function getAllJobStatus($keyword)
    {
        return $this->model->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
          $query->where('name','LIKE','%'.$keyword.'%');
        })->get();     
    }

    /**
     * Get All Job Status With Paginate
     * 
     * @param string $keyword
     * @param int $show
     * @return \App\Models\CompanyJobStatus
     */
    public function getPaginateJobStatus($keyword, $show)
    {
      return $this->model->when($keyword != null && $keyword != '', function ($query) use ($keyword) {
        $query->where('name','LIKE','%'.$keyword.'%');
      })->paginate($show);
    }

    /**
     * Create New Job Status Data
     * 
     * @param array $data
     * @return \App\Models\CompanyJobStatus
     */
    public function createJobStatus(array $data)
    {
      return $this->model->create($data);
    }

    /**
     * Update Existing Job Status
     * 
     * @param array $data
     * @param int $id
     * @return boolean
     */
    public function updateJobStatus(array $data, $id)
    {
      return $this->model->findOrFail($id)->update($data);
    }

    /**
     * Get One Data Job Status By ID
     * 
     * @param int $id
     * @return \App\Models\CompanyJobStatus
     */
    public function detailJobStatus($id)
    {
      return $this->model->findOrFail($id);
    }

    /**
     * Delete Job Status By Id
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteJobStatus($id)
    {
      return $this->model->findOrFail($id)->delete();
    }
}