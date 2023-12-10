<?php

namespace App\Traits;

use App\Jobs\NotificationJob;
use App\Models\User;

trait NotificationTrait
{
	/**
	 * DISPATCHING A NOTIFICATION TO MANAGER JOB
	 * @param User $employee - Employee has send a notification
	 * @param string $title - Title of notification
	 * @param string $message - Message of notification
	 * @param string $model - Model of notification
	 * @param mixed $modelId - ID Of model (Nullable)
	 * @return void
	 */
	public static function dispatchNotificationToManager(User $employee, $title, $message, $model, $modelId = null)
	{
		$dataNotif = [
			'branch'    => branchSelected('sanctum:employee')->id,
			'employee'  => $employee->id,
			'model'     => $model,
			'model_id'  => !is_null($modelId) ? $modelId : null,
			'title'     => $title,
			'message'   => $message
		];
		NotificationJob::dispatch(NOTIF_MANAGER, $dataNotif);
	}

	/**
	 * DISPATCHING A NOTIFICATION TO EMPLOYEE JOB
	 * @param User $employee - Employee has send a notification
	 * @param string $title - Title of notification
	 * @param string $message - Message of notification
	 * @param string $model - Model of notification
	 * @param mixed $modelId - ID Of model (Nullable)
	 * @param string $type - The Type (FOR EMPLOYEE PAID LEAVE)
	 * @return void
	 */
	public static function dispatchNotificationToEmployee(User $employee, $title, $message, $model, $modelId = null, $type = null)
	{
		$dataNotif = [
			'branch'    => branchSelected('sanctum:manager')->id,
			'employee'  => $employee->id,
			'model'     => $model,
			'model_id'  => !is_null($modelId) ? $modelId : null,
			'type'			=> !is_null($type) ? $type : null,
			'title'     => $title,
			'message'   => $message
		];
		NotificationJob::dispatch(NOTIF_EMPLOYEE, $dataNotif);
	}
}
