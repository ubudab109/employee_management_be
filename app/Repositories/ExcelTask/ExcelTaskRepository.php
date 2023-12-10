<?php

namespace App\Repositories\ExcelTask;

use App\Models\ExcelTask;

class ExcelTaskRepository implements ExcelTaskInterface
{
	/**
	 * @var ModelName
	 */
	protected $model;

	public function __construct(ExcelTask $model)
	{
		$this->model = $model;
	}

	/**
	 * GET LIST TASK EXCEL
	 * @param string $model - Model of excel
	 * @param string $type - Type of task excel (EXCEL_EXPORT or EXCEL_IMPORT)
	 * @param int $managerId - Manager id
	 * @param string $status - Status of excel task
	 * @return Collection
	 */
	public function listExcelTask($model, $type, $managerId, $status)
	{
		return $this->model
		->where('branch_id', branchSelected('sanctum:manager')->id)
		->when($model != null, function ($query) use ($model) {
			$query->where('source_type', $model);
		})
		->when($type != null, function ($query) use ($type) {
			$query->where('type', $type);
		})
		->when($managerId != null, function ($query) use ($managerId) {
			$query->where('manager_id', $managerId);
		})
		->when($status != null, function ($query) use ($status) {
			$query->where('status', $status);
		})
		->orderBy('id', 'desc')
		->with('manager:id,name')
		->get();
	}

	/**
     * GET DETAIL EXCEL TASK
     * @param int $taskId - ID of task
     * @return object
     */
	public function detailExcelTask($taskId)
	{
		return $this->model->find($taskId);
	}

	/**
     * CREATE NEW EXCEL TASK
     * @param array $data
     * @return object
     */
	public function createExcelTask(array $data)
	{
		return $this->model->create($data);
	}
}
