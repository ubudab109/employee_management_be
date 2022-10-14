<?php

namespace App\Services;

use App\Repositories\CompanyDivision\CompanyDivisionInterface;
use Illuminate\Support\Facades\DB;

class CompanyDivisionServices
{
    public $companyDivisionRepository;

    public function __construct(CompanyDivisionInterface $companyDivisionRepository)
    {
        $this->companyDivisionRepository = $companyDivisionRepository;
    }

    /**
     * It will return a paginated list of divisions if the parameter show is not equal to all,
     * otherwise it will return a list of divisions
     * 
     * @param array $param - Parameter to search or filter 
     */
    public function list($param = [])
    {
        if (isset($param['show']) && $param['show'] != 'all') {
            $data = $this->companyDivisionRepository->getPaginateDivision(
                isset($param['branch_id']) && $param['branch_id'] != null ? $param['branch_id'] : null,
                isset($param['keyword']) && $param['keyword'] != null ? $param['keyword'] : null,
                $param['show'] == null ? 10 : $param['show'],
            );
            $type = 'paginate';
        } else {
            $data = $this->companyDivisionRepository->getAllDivision(
                isset($param['branch_id']) && $param['branch_id'] != null ? $param['branch_id'] : null,
                isset($param['keyword']) && $param['keyword'] != null ? $param['keyword'] : null,
            );
            $type = 'list';
        }

        return [
            'status'    => true,
            'type'      => $type,
            'data'      => $data, 
        ];
    }

    /**
     * It creates a new division in the database.
     * 
     * @param array $data - Data to input to database 
     * 
     * @return object
     */
    public function create($data = [])
    {
        DB::beginTransaction();
        try {
            $data['branch_id'] = branchSelected('sanctum:manager')->id;
            $created = $this->companyDivisionRepository->storeDivision($data);
            DB::commit();
            return [
                'status'    => true,
                'data'      => $created,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status' => false,
                'data'   => 'Internal Server Error',
            ];
        }
    }

    /**
     * It updates the division data in the database
     * 
     * @param array $data - array of data to be updated
     * @param int $divisionId The id of the division to be updated
     * 
     * @return object.
     */
    public function update($data = [], $divisionId)
    {
        DB::beginTransaction();
        try {
            $data = $this->companyDivisionRepository->updateDivision($data, $divisionId);
            DB::commit();
            return [
                'status'    => true,
                'data'      => $data,
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            return [
                'status'    => false,
                'data'      => null,
            ];
        }
    }

    /**
     * It returns a list of divisions for a company
     * 
     * @param int $divisionId - The id of the division you want to get the details of.
     *
     * @return object
     */
    public function detail($divisionId)
    {
        $data = $this->companyDivisionRepository->detailDivision($divisionId);
        if (is_null($data)) {
            return [
                'status'    => false,
                'code'      => 404,
                'data'      => 'Data Not Found'
            ];
        } 

        return [
            'status'    => true,
            'code'      => 200,
            'data'      => $data,
        ];
    }

    /**
     * It deletes a division.
     * 
     * @param int $divisionId - The id of the division to be deleted
     * 
     * @return bool.
     */
    public function delete($divisionId)
    {
        return $this->companyDivisionRepository->deleteDivision($divisionId);
    }

}