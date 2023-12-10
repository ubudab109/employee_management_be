<?php

namespace App\Services;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationManagerServices
{
    /**
     * INSERT DATA NOTIFICATION
     * THE INSERTED DATA WILL SEND TO MANAGER THROUGH JOB
     * @param array $data
     * @return array
     */
    public static function sendNotification(array $data)
    {
        DB::beginTransaction();
        try {
            DB::table('notification_manager')->insert([
                'branch_id'       => $data['branch'],
                'employee_id'     => $data['employee_id'],
                'user_manager_id' => $data['user_manager_id'],
                'model_type'      => $data['model'],
                'model_id'        => $data['model_id'],
                'fe_url'          => getFeEndpointNotification($data['model'], $data['employee_id']),
                'title'           => $data['title'],
                'message'         => $data['message'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            DB::commit();
            return [
                'status'  => true,
                'message' => 'Notification sended successfully',
            ];
        } catch (\Exception $err) {
            Log::error($err->getMessage().' '. $err->getLine());
            DB::rollBack();
            return [
                'status'  => false,
                'message' => "Failed to send notification ".$err->getMessage(),
            ];
        }
    }
}