<?php

namespace App\Repositories\CompanySetting;

interface CompanySettingInterface
{
  /**
   * This function returns the result of the get() function of the model.
   * 
   * @return array.
   */
  public function listCompanySetting();

  /**
   * It returns the paginated results of the model
   * 
   * @param int $show The number of items to show per page.
   * 
   * @return Collection.
   */
  public function listPaginateCompanySetting($show);

  /**
   * It returns the first row from the database where the setting_key column is equal to the 
   * parameter
   * 
   * @param int $key The key of the setting you want to get.
   * 
   * @return object.
   */
  public function getCompanySetting($key);

  /**
   * It returns the value of a setting key from the database
   * 
   * @param int $key The key of the setting you want to get the value of.
   * 
   * @return object.
   */
  public function getValueCompanySetting($key);

  /**
   * It updates the value of a setting if it exists, or creates a new setting if it doesn't
   * 
   * @param int $key The key of the setting you want to update.
   * @param int $value The value to be stored in the database.
   * 
   * @return bool.
   */
  public function updateCompanySetting($key, $value);
}
