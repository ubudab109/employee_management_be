<?php

use App\Models\CompanySetting;

/**
 * HELPERS SERVICE APPLICATION
 * DEVELOPER: MUHAMMAD RIZKY FIRDAUS
 * DEV DATE: 14/01/2022
 * 
 * MODIFY WITH YOUR OWN RISK
 * YOU CAN ADD MORE HELPERS FUNCTION IN HERE
 */

 /**
  * Get All Company Setting
  * @param array $array
 */
 function allCompanySetting($array = null) 
 {
    if(!isset($array[0])) {
      $allSettings = CompanySetting::get();
      if ($allSettings) {
        $output = [];
        foreach ($$allSettings as $setting) {
          $output[$setting->key] = [
            'setting_name'  => $setting->setting_name,
            'setting_value' => $setting->value
          ];
        }
        return $output;
      }
      return false;
    } else if (is_array($array)) {
      $allSettings = CompanySetting::where('setting_key', $array)->get();
      if ($allSettings) {
        $output = [];
        foreach ($$allSettings as $setting) {
          $output[$setting->key] = [
            'setting_name'  => $setting->setting_name,
            'setting_value' => $setting->value
          ];
        }
        return $output;
      }
      return false;
    } else {
      $allsettings = CompanySetting::where(['setting_key' => $array])->first();
        if ($allsettings) {
            $output = $allsettings->value;
            return $output;
        }
        return false;
    }
 }

 /**
  * Get One Company Setting
  * @param string $keys
 */
 function settings($keys) 
 {
    $setting = CompanySetting::where('setting_key', $keys)->first();
    return empty($setting) ? false : $setting->value;
 }

 /**
  * Generate image name
  * @param String $extension
  * @return preg_replace random name
  */
 function generateImageName($extension) {
  return preg_replace('/(0)\.(\d+) (\d+)/', '$3$1$2', microtime()).'.'.$extension;
 }

 /**
  * Storing images to strage
  * @param Path to store
  * @param File
  * @return String file name
  */
 function storeImages($path, $file) {
  $extension = $file->getClientOriginalExtension();
  $imageName = generateImageName($extension);
  $file->storeAs(
      $path, $imageName
  );
  return $imageName;
}