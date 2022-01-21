<?php

namespace App\Repositories\CompanySetting;

interface CompanySettingInterface
{
  public function listCompanySetting(); 
  public function listPaginateCompanySetting($show); 
  public function getCompanySetting($key);
  public function getValueCompanySetting($key);
  public function updateCompanySetting($key, $value);

}