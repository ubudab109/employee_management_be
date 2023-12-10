<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationEmployeeServices
{
    /**
     * INSERT DATA NOTIFICATION
     * THE INSERTED DATA WILL SEND TO EMPLOYEE THROUGH JOB
     * @param array $data
     * @return array
     */
    public static function sendNotification(array $data)
    {
        DB::beginTransaction();
        try {
            if ($data['type'] != null) {
                $icon = getNotifIcon($data['model'], $data['type']);
            } else {
                $icon = getNotifIcon($data['model'], null);
            }
            DB::table('notification_employee')->insert([
                'branch_id'     => $data['branch'],
                'employee_id'   => $data['employee'],
                'model_type'    => $data['model'],
                'model_id'      => $data['model_id'],
                'title'         => $data['title'],
                'message'       => $data['message'],
                'icon'          => $icon,
                'created_at'    => now(),
                'updated_at'    => now(),
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