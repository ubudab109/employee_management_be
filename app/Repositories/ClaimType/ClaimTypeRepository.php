<?php

namespace App\Repositories\ClaimType;

use App\Models\ClaimType;

class ClaimTypeRepository implements ClaimTypeInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(ClaimType $model)
    {
      $this->model = $model;
    }

    /**
     * LIST CLAIM TYPE FOR EACH BRANCH
     * @param string $keyword - FOR SEARCHING BY NAME IN CLAIM TYPE
     * @return array
     */
    public function listClaimType($keyword)
    {
      return $this->model->where('branch_id', branchSelected('sanctum:manager')->id)
      ->when(!is_null($keyword) && $keyword != '', function ($query) use ($keyword) {
        $query->where('name', 'LIKE', '%'.$keyword.'%');
      })
      ->get();
    }

    /**
     * DETAIL CLAIM TYPE
     * @param integer $claimTypeId
     * @return object
     */
    public function detailClaimType($claimTypeId)
    {
      return $this->model->find($claimTypeId);
    }

    /**
     * CREATE NEW CLAIM TYPE
     * @param array $data
     * @return object
     */
    public function createClaimType(array $data)
    {
      return $this->model->create($data);
    }

    /**
     * UPDATE CLAIM TYPE
     * @param array $data - DATA TO UPDATE
     * @param integer $claimTypeId - CLAIM TYPE ID
     * @return bool
     */
    public function updateClaimType(array $data, $claimTypeId)
    {
      return $this->model->find($claimTypeId)->update($data);
    }

    /**
     * DELETE CLAIM TYPE
     * @param integer $claimTypeId - CLAIM TYPE ID
     * @return object
     */
    public function deleteClaimType($claimTypeId)
    {
      return $this->model->find($claimTypeId)->delete();
    }
}