<?php

namespace App\Services;

use App\Repositories\ClaimType\ClaimTypeInterface;

class ClaimTypeService
{
    public $claimType;

    public function __construct(ClaimTypeInterface $claimType)
    {
        $this->claimType = $claimType;
    }

    /**
     * LIST CLAIM TYPE
     * @param array $param
     * @return array
     */
    public function listClaimType($param = [])
    {
        $data = $this->claimType->listClaimType(
            isset($param['keyword']) ? $param['keyword'] : null
        );

        return [
            'status'  => true,
            'message' => 'Data Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * DETAIL CLAIM TYPE
     * @param integer $claimTypeId
     * @return object
     */
    public function detail($claimTypeId)
    {
        $data = $this->claimType->detailClaimType($claimTypeId);
        if (!$data) {
            return [
                'status'  => false,
                'message' => 'Claim Type Not Found',
                'data'    => null,
            ];
        }

        return [
            'status'  => true,
            'message' => 'Claim Type Fetched Successfully',
            'data'    => $data,
        ];
    }

    /**
     * CREATE CLAIM TYPE
     * @param array $data
     * @return object
     */
    public function create(array $data)
    {
        return [
            'status'  => true,
            'message' => 'Claim Type Created Successfuly',
            'data'    => $this->claimType->createClaimType($data),
        ];
    }

    /**
     * UPDATE CLAIM TYPE
     * @param array $data - DATA TO UPDATED
     * @param integer $claimTypeId
     * @return object
     */
    public function update(array $data, $claimTypeId)
    {
        $this->claimType->updateClaimType($data, $claimTypeId);
        return [
            'status'  => true,
            'message' => 'Claim Type Updated Successfuly',
        ];
    }

    /**
     * DELETE CLAIM TYPE
     * @param integer $claimTypeId
     * @return object
     */
    public function delete($claimTypeId)
    {
        $this->claimType->deleteClaimType($claimTypeId);
        return [
            'status'  => true,
            'message' => 'Claim Type Deleted Successfully',
        ];
    }
}