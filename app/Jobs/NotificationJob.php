<?php

namespace App\Jobs;

use App\Models\UserManager;
use App\Services\NotificationEmployeeServices;
use App\Services\NotificationManagerServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type;
    protected $data = [];
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type, array $data)
    {
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch($this->type) {
            case NOTIF_EMPLOYEE:
                return $this->sendNotificationEmployee();
            case NOTIF_MANAGER:
                return $this->sendNotificationManager();
            default:
                return null;
        }
    }

    public function sendNotificationEmployee()
    {
        $data = [
            'branch'            => $this->data['branch'],
            'employee'          => $this->data['employee'],
            'model'             => $this->data['model'],
            'model_id'          => $this->data['model_id'],
            'type'              => $this->data['type'],
            'title'             => $this->data['title'],
            'message'           => $this->data['message'],
        ];
        NotificationEmployeeServices::sendNotification($data);
    }

    public function sendNotificationManager()
    {
        $userManagers = UserManager::all();
        
        foreach ($userManagers as $manager) {
            $scopeName = getScopePermissionClass($this->data['model']);
            if (isScopeAccess($manager, $scopeName)) {
                $data = [
                    'branch'            => $this->data['branch'],
                    'employee_id'       => $this->data['employee'],
                    'user_manager_id'   => $manager->id,
                    'model'             => $this->data['model'],
                    'model_id'          => $this->data['model_id'],
                    'title'             => $this->data['title'],
                    'message'           => $this->data['message'],
                ];
                NotificationManagerServices::sendNotification($data);
            }
        }
    }
}
