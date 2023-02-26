<?php

namespace App\Services;

use App\Repositories\SalaryComponent\SalaryComponentInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryComponentServices
{
    protected $salaryComponent;

    public function __construct(SalaryComponentInterface $salaryComponent)
    {
        $this->salaryComponent = $salaryComponent;
    }

    /**
     * LIST SALARY COMPONENT WITHOUT PAGINATE
     * @param array $data
     * @return object
     */
    public function list($param = [])
    {
        $data = $this->salaryComponent->listSalaryComponent(
            isset($param['keyword']) && $param['keyword'] !== null ? $param['keyword'] : '',
            isset($param['type']) && $param['type'] !== null ? $param['type'] : '',
        );

        return [
            'status'    => true,
            'message'   => 'Salary Component List Fetched Successfully',
            'data'      => $data,
        ];
    }

    /**
     * CREATE SALARY COMPONENT
     * @param array $data
     * @return object
     */
    public function create($data = []) 
    {
        DB::beginTransaction();
        try {
            $this->salaryComponent->createSalaryComponent($data);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Salary Component Created Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error',
            ];
        }
        
    }

    /**
     * UPDATE SALARY COMPONENT
     * @param array $data
     * @param int $id
     * @return object
     */
    public function update($data = [], $id)
    {
        DB::beginTransaction();
        try {
            $this->salaryComponent->updateSalaryComponent($data, $id);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Salary Component Created Successfully',
            ];
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            return [
                'status'  => false,
                'message' => 'Internal Server Error',
            ];
        }
    }
}