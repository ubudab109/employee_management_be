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

    /**
     * This function returns the result of the get() function of the model.
     * 
     * @return array.
     */
    public function listCompanySetting()
    {
      return $this->model->get();
    }

    /**
     * It returns the paginated results of the model
     * 
     * @param int $show The number of items to show per page.
     * 
     * @return Collection.
     */
    public function listPaginateCompanySetting($show)
    {
      return $this->model->paginate($show);
    }

    /**
     * It returns the first row from the database where the setting_key column is equal to the 
     * parameter
     * 
     * @param int $key The key of the setting you want to get.
     * 
     * @return object.
     */
    public function getCompanySetting($key)
    {
      return $this->model->where('setting_key',$key)->first();  
    }

    /**
     * It returns the value of a setting key from the database
     * 
     * @param int $key The key of the setting you want to get the value of.
     * 
     * @return object.
     */
    public function getValueCompanySetting($key)
    {
      return $this->model->where('setting_key',$key)->select('id','setting_key','value')->first();  
    }

    /**
     * It updates the value of a setting if it exists, or creates a new setting if it doesn't
     * 
     * @param int $key The key of the setting you want to update.
     * @param int $value The value to be stored in the database.
     * 
     * @return bool.
     */
    public function updateCompanySetting($key, $value)
    {
      return $this->model->updateOrCreate(
        ['setting_key' => $key],
        ['value' => $value],
      );
    }
}