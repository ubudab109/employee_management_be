<?php

namespace App\Repositories\CompanySetting;

use App\Models\CompanySetting;

class CompanySettingRepository implements CompanySettingInterface
{
    /**
    * @var ModelName
    */
    protected $model;

    public function __construct(CompanySetting $model)
    {
      $this->model = $model;
    }

    public function listCompanySetting()
    {
      return $this->model->get();
    }

    public function listPaginateCompanySetting($show)
    {
      return $this->model->paginate($show);
    }

    public function getCompanySetting($key)
    {
      return $this->model->where('setting_key',$key)->first();  
    }

    public function getValueCompanySetting($key)
    {
      return $this->model->where('setting_key',$key)->select('id','setting_key','value')->first();  
      
    }

    public function updateCompanySetting($key, $value)
    {
      return $this->model->updateOrCreate(
        ['setting_key' => $key],
        ['value' => $value],
      );
    }
}